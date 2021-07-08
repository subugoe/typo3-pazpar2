
function choose_url (data, proxyPattern) {
    //first try to prepare local_url from recipe
    var local_url = data["md-url_recipe"] !== undefined
        ? prepare_url(data["md-url_recipe"][0], data) : null;

    var use_url_proxy = data["md-use_url_proxy"] !== undefined
        ? data["md-use_url_proxy"] : "0";

    //use the proxyPattern
    if (proxyPattern && use_url_proxy == "1") {
        if (local_url) {
            data["md-local-url"] = [];
            data["md-local-url"].push(local_url);
        }
        var ref_local_url = prepare_url(proxyPattern, data);
        if (ref_local_url) return ref_local_url;
    }

    // proxyPattern failed, go for local
    if (local_url)
        return local_url;

    //local failed, go for resource
    return data["md-electronic-url"] !== undefined
        ? data["md-electronic-url"][0] : null;
}

var XRef = function (url, text) {
  this.url = url;
  this.text = text;
};

function has_recipe (data) {
  var has = false;
  if (data["md-url_recipe"] !== undefined) {
     var recipe = data["md-url_recipe"][0];
     if (typeof recipe == "string" && recipe.length>0) {
       has = true;
     }
  }
  return has;
}

function getUrlFromRecipe (data) {
  if (has_recipe(data)) {
    return prepare_url(data["md-url_recipe"][0],data);
  } else {
    return null;
  }
}

function getElectronicUrls (data) {
  var urls = [];
  if (data["md-electronic-url"] !== undefined) {
    for (var i=0; i<data["md-electronic-url"].length; i++) {
      var linkUrl = data["md-electronic-url"][i];
      var linkText = (data["md-electronic-text"]) ? data["md-electronic-text"][i] : "Web Link";
      var ref = new XRef(linkUrl, (linkText.length==0 ? "Web Link" : linkText));
      urls.push(ref);
    }
  }
  return urls;
}


// Prepares urls from recipes with expressions in the form:
// ${variable-name[pattern/replacement/mode]}, [regex] is optional
// eg. http://sever.com?title=${md-title[\s+//]} will strip all whitespaces
function prepare_url(url_recipe, meta_data) {
    if (typeof url_recipe != "string" || url_recipe.length == 0)
        return null;
    if (typeof meta_data != "object")
        return null;
    try {
        var result =  url_recipe.replace(/\${[^}]*}/g, function (match) {
          return get_var_value(match, meta_data);
          }
        );
        //url encoding for %{} vars
        result =  result.replace(/\%{[^}]*}/g, function (match) {
          return encodeURIComponent(get_var_value(match, meta_data));
          }
        );
        return result;
    } catch (e) {
        return "Malformed URL recipe: " + e.message;
    }
}

function get_var_value (expr_in, meta_data) {
    //strip ${ and }
    var expr = expr_in.substring(2, expr_in.length-1)
    if (expr == "") return "";
    //extract name
    var var_name = expr.match(/^[^\[]+/)[0];
    if (typeof meta_data[var_name] == "undefined") return "";
    else var var_value = meta_data[var_name][0];
    if (var_name.length < expr.length) { //possibly a regex
       var_value = exec_sregex(
          expr.substring(var_name.length+1, expr.length-1),
          var_value);
    }
    return var_value;
}

// exec perl-like substitution regexes in the form: pattern/replacement/mode
function exec_sregex (regex_str, input_str) {
    var regex_parts = ["", "", ""];
    var i = 0;
    for (var j=0; j<regex_str.length && i<3; j++) {
        if (j>0 && regex_str.charAt(j) == '/' && regex_str.charAt(j-1) != '\\')
            i++;
        else
            regex_parts[i] += regex_str.charAt(j);
    }
    var regex_obj = new RegExp(regex_parts[0], regex_parts[2]);
    return input_str.replace(regex_obj, regex_parts[1]);
}

function test_url_recipe() {
  var url_recipe = "http://www.indexdata.com/?title=${md-title[\\s+/+/g]}&author=${md-author}";
  var meta_data = { "md-title" : ["Art of Computer Programming"], "md-author" : ["Knuth"]}
  var final_url = prepare_url(url_recipe, meta_data);
  alert(final_url);
}

/*
 * autocomplete
 *
 */

spAutoCompleteURLs = {};
function pp2_autocomplete_init(id, type) {
    if (!id)
	id = "input#pz2-field-all";
    if (!type)
	type = "all";

    if ($(id).length <= 0) {
        console.log("Cannot find tag id in HTML page: " + id);
        return;
    }

   /*
   var words = {
	author: ["Adam", "John", "Mike", "Charles"],
        title: ["Berlin", "Copenhagen", "Aarhus", "Boston", "Chicago", "Katowice" ],
        all: ["apple", "beer", "cocktail", "vine", "wein", "vino", "bira", "ipa", "bock", "pilsner", "applewoi" ],
	beer: ["Alt", "Altbier", "Amber ale", "American Amber Ale", "Barley Wine", "Berliner Weisse", "Bitter", "Bière de Garde", "Brown Ale", "Brown Porter", "California Common Beer", "Cream Ale", "Doppelbock", "Dunkelweiizen", "Eisbock", "English Barleywine", "English Brown Ale", "English IPA", "Export / Dortmunder", "German Pilsner (Pils)", "Golden Ales", "Lambic", "Light Bitters", "Mild and brown ale", "Mild", "Northern German Altbier", "Old Ale", "Pale Ale or IPA", "Porter &amp; Stout", "Roggenbier (German Rye Beer)", "Standard/Ordinary Bitter", "Steam beer", "Weizen/Weissbier", "Weizenbock"]
   };
   */

   /*
   var urls = {
	author: "http://typo3-devel.indexdata.com/api/author.json",
        title:  "http://typo3-devel.indexdata.com/api/title.json",
        all: "http://typo3-devel.indexdata.com/api/all.json",
	beer: "http://typo3-devel.indexdata.com/api/all.json"
   };
   */

    var urls = spAutoCompleteURLs;

    if (urls[type]) {
	$(id).autocomplete({ source: urls[type] });
    } else {
	console.log("unknown autocomplete type configuration: " + type);
   }
};

/* wait a half second for DOM ready */
function pp2_autocomplete_init_wrapper(id, type) {
    setTimeout(function() { pp2_autocomplete_init(id, type); }, 500);
}

/* wrapper to enable all, author and title autocomplete suggestions */
function pp2_autocomplete_init_all () {
    pp2_autocomplete_init(); // all
    pp2_autocomplete_init('input#pz2-field-title', 'title');
    pp2_autocomplete_init('input#pz2-field-person', 'author');
}
