# SUB Icon Font

Designed by Henrik Cederblad, [Cederblad Design](http://cederbladdesign.com).

![alt tag](https://raw.github.com/subugoe/sub-iconfont/master/Workfiles/_misc/SUB_Icon_Font_Splash_v1.png)

## About

This is an icon font that contains UI symbols for various types of media and information sources. It was commissioned in 2013 by [SUB Göttingen](http://www.sub.uni-goettingen.de) | Georg-August-Universität
Niedersächsische Staats- und Universitätsbibliothek.

## Package Contents

**`SUB-Icon-Font/`**

A folder containing the actual icon font:

- **eot**, **woff**, **svg**, **ttf** standard file formats
- **Demo** showing an example webpage with the font in use

**`Icons_and_Guidelines.pdf`**

An overview of the icons along with information about the guidelines for designing and using the icon font:

- **Icons overview**: showing all icons together on a grid
- **Design Principles**: describing rules in effect during original design (should be followed if the set is to be extended with new icons)
- **Usage Guidelines**: notes about proper display

**`Character_Map.md`**

- Assigned characters for each icon (Unicode)
- CSS classes

**`License.txt`**

**`ReadMe.md`**


## Usage

There are multiple ways to implement icon fonts on a web page. You may take a look at the included demo – open **index.html** within **SUB Icon Font/** – and examine the source code for a few examples. (N.B. this example does not align and stack each icon's background/foreground symbols one on top of the other, see *Layers* below.)

### Character mapping

The glyphs are mapped to Private Use Area ([PUA](http://en.wikipedia.org/wiki/Private_Use_Area)) characters of Unicode. See **Character_Map.md** for assigned codes.

### Layers

SUB Icon Font is designed to use two layered symbols per icon: a background layer and a foreground layer. These should be positioned one on top of the other with CSS. It doesn't matter which one is on top since their graphics will not overlap.

### Colors

The color (luminosity) of background layers should always be darker than the foreground layers, otherwise the icons won't appear correctly.

## License

Licensed under the MIT License (see *License.txt*).