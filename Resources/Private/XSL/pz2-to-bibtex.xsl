<?xml version="1.0" encoding="UTF-8"?>
<!--
	Sloppily converts pazpar2 metadata to BibTeX.
	
	2011-07 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:pz="http://www.indexdata.com/pazpar2/1.0">
	
	<!--
		We just pass on non-ASCII characters in UTF-8 encoding for the time being.
		To get proper, interoperable, BibTeX using TeX commands would be better. 
	-->
	<xsl:output method="text" encoding="UTF-8"/>


	<xsl:template match="//location">
		<!--
			Record type:
			Try to map our pazpar2 media types to corresponding BibTeX record types.
		-->
		<xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="md-medium='book'">book</xsl:when>
				<xsl:when test="md-medium='electronic'">electronic</xsl:when>
				<xsl:when test="md-medium='website'">webpage</xsl:when>
				<xsl:when test="md-medium='journal'">periodical</xsl:when>
				<xsl:when test="md-medium='article'">article</xsl:when>
				<!--
					The otherwise case includes, in particular, the pazpar2
					media-types 'audio-visual', 'map', 'microform', 
					'music-score', 'recording' and 'other'.
				-->
				<xsl:otherwise>misc</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:text>@</xsl:text>
		<xsl:value-of select="$type"/>
		<xsl:text>{</xsl:text>
		


		<!--
			Cite Key:
			Use database and record IDs, remove some forbidden characters.
		-->
		<xsl:value-of select="translate(concat(@id, '/', md-id), ' ,{}()[]#%@', '')"/>
		<xsl:text>,&#10;</xsl:text>
		

		<!--
			Title:
			md-title
			md-title-remainder
			md-title-number-section (sloppy, it’d be preferable if we had those 
				infos separately, so we could use BibTeX’s more specific fields).
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Title</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-title">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
				<xsl:if test="md-title and md-title-number-section">
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:for-each select="md-title-number-section">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
				<xsl:if test="md-title|md-title-number-section">
					<xsl:if test="md-title-remainder">
						<xsl:text> </xsl:text>
					</xsl:if>
				</xsl:if>
				<xsl:for-each select="md-title-remainder">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		

		<!--
			Series:
			md-series-title
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Series</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-series-title">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		
		
		<!--
			Author:
			md-author
			
			Other-Person:
			md-other-person
			As we cannot reliably recognise editors and other-person entries may
			be any of a wide range of functions, add all of them to the
			non-standard Other-Person field, so the names are part of the record
			for convenient copy and pasting but will not be visible in standard
			BibTeX processing.
			
			Names are separated by ' and ' in both fields.
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Author</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-author">
					<xsl:if test="position()&gt;1">
						<xsl:text> and </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Other-Person</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-other-person">
					<xsl:if test="position()&gt;1">
						<xsl:text> and </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		
		<!--
			Year:
			md-date
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Year</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-date">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		
		
		<!--
			Annote:
			md-description, separated by two LFs
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Annote</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-description">
					<xsl:if test="position()&gt;1">
						<xsl:text>&#10;&#10;</xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		
		<!--
			Abstract:
			md-abstract, separated by two LFs
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Abstract</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-abstract">
					<xsl:if test="position()&gt;1">
						<xsl:text>&#10;&#10;</xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		
		<!--
			Journal:
			md-journal-title
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Journal</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-journal-title">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		
		<!--
			Article information.
			Distinguish two cases:
				a) we do have detailed volume/issue/pages information:
					md-volume-number -> Volume
					md-issue-number -> Number
					md-pages -> Pages
				b) we don’t have detailed volume/issue/pages information,
					(wrongly) map all we have to the volume field:
					md-journal-subpart -> Volume
		-->
		<xsl:choose>
			<xsl:when test="md-volume-number">
				<xsl:call-template name="BibTeXLineMaker">
					<xsl:with-param name="key">Volume</xsl:with-param>
					<xsl:with-param name="value">
						<xsl:for-each select="md-volume-number">
							<xsl:if test="position()&gt;1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="."/>
						</xsl:for-each>
					</xsl:with-param>
				</xsl:call-template>

				<xsl:call-template name="BibTeXLineMaker">
					<xsl:with-param name="key">Number</xsl:with-param>
					<xsl:with-param name="value">
						<xsl:for-each select="md-issue-number">
							<xsl:if test="position()&gt;1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="."/>
						</xsl:for-each>
					</xsl:with-param>
				</xsl:call-template>
		
				<xsl:call-template name="BibTeXLineMaker">
					<xsl:with-param name="key">Pages</xsl:with-param>
					<xsl:with-param name="value">
						<xsl:for-each select="md-pages">
							<xsl:if test="position()&gt;1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="."/>
						</xsl:for-each>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			
			<xsl:otherwise>
				<xsl:call-template name="BibTeXLineMaker">
					<xsl:with-param name="key">Volume</xsl:with-param>
					<xsl:with-param name="value">
						<xsl:for-each select="md-journal-subpart">
							<xsl:if test="position()&gt;1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="."/>
						</xsl:for-each>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
		
		
		<!--
			Publication information:
			md-publication-name -> Publisher
			md-publication-place -> Address
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Publisher</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-publication-name">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Address</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-publication-place">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		
		
		<!--
			ISBN/ISSN:
			md-isbn -> Isbn
			md-issn -> Issn
			md-pissn -> Issn
			md-eissn -> Issn
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Isbn</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-isbn">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Issn</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-issn|md-pissn|md-eissn">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>


		
		<!--
			Doi:
			md-doi
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Doi</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-doi">
					<xsl:if test="position()&gt;1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

		
		<!--
			Url:
			md-electronic-url
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Url</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-electronic-url">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		
		
		<!--
			Catalogue-Link:
			md-catalogue-url - add custom field to link to the catalogue
		-->
		<xsl:call-template name="BibTeXLineMaker">
			<xsl:with-param name="key">Catalogue-Link</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:for-each select="md-catalogue-url">
					<xsl:if test="position()&gt;1">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
		
		
		<!-- Closing } to end BibTeX record. -->
		<xsl:text>}&#10;&#10;</xsl:text>

	</xsl:template>


	<xsl:template name="BibTeXLineMaker">
		<xsl:param name="key"/>
		<xsl:param name="value"/>
		
		<xsl:if test="$key!='' and $value!=''">
			<xsl:text>&#9;</xsl:text>
			<xsl:value-of select="$key"/>
			<xsl:text> = {</xsl:text>
			<xsl:value-of select="$value"/>
			<xsl:text>},&#10;</xsl:text>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
