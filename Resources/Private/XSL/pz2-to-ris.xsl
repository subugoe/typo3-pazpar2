<?xml version="1.0" encoding="UTF-8"?>
<!--
	Converts pazpar2 metadata to RIS.
	
	RIS format:
		http://www.refman.com/support/risformat_intro.asp
		http://www.adeptscience.co.uk/kb/article/FE26
	
	2011-07 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:pz="http://www.indexdata.com/pazpar2/1.0">
	
	<!--
		The RIS format is specified to be encoded using the "Windows ANSI 
		Character Set". However, big RIS creators (e.g. SpringerLink) seem to
		use UTF-8 encoded text. We break the specification in the same way, so
		we can encode a wider range of characters.
	-->
	<xsl:output method="text" encoding="UTF-8"/>


	<xsl:template match="//location">
		<!-- 
			Record type:
			Try to map our pazpar2 media types to corresponding RIS record types.
		
			RIS TYpe field needs to be at beginning of the record.
		-->
		<xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="md-medium='audio-visual'">ADVS</xsl:when>
				<xsl:when test="md-medium='book'">BOOK</xsl:when>
				<xsl:when test="md-medium='electronic'">ELEC</xsl:when>
				<xsl:when test="md-medium='website'">ICOMM</xsl:when>
				<xsl:when test="md-medium='journal'">JFULL</xsl:when>
				<xsl:when test="md-medium='article'">JOUR</xsl:when>
				<xsl:when test="md-medium='map'">MAP</xsl:when>
				<xsl:when test="md-medium='music-score'">MUSIC</xsl:when>
				<xsl:when test="md-medium='recording'">SOUND</xsl:when>
				<!--
					The otherwise case includes, in particular, the pazpar2
					media-types 'microform' and 'other'.
				-->
				<xsl:otherwise>GEN</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:call-template name="RISLineMaker">
			<xsl:with-param name="key">TY</xsl:with-param>
			<xsl:with-param name="value" select="$type"/>
		</xsl:call-template>


		<!--
			ID:
			Use database and record IDs. Not imported.
		-->
		<xsl:call-template name="RISLineMaker">
			<xsl:with-param name="key">ID</xsl:with-param>
			<xsl:with-param name="value">
				<xsl:value-of select="@id"/>
				<xsl:text>: </xsl:text>
				<xsl:value-of select="md-id"/>
			</xsl:with-param>
		</xsl:call-template>
		

		<!-- 
			Title:
			md-title -> T1
			md-title-remainder -> T2
			md-series-title -> T3
		-->
		<xsl:for-each select="md-title">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">T1</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		<xsl:for-each select="md-title-number-section">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">T3</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		<xsl:for-each select="md-title-remainder">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">T2</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		<xsl:for-each select="md-series-title">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">T3</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			People:
			md-author -> A1
			md-other-person -> A2
		-->
		<xsl:for-each select="md-author">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">A1</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		<xsl:for-each select="md-other-person">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">A2</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			Date:
			Map the first four characters of md-date to Y1 and include the 
			slashes specified by RIS. 
		-->
		<xsl:for-each select="md-date">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">Y1</xsl:with-param>
				<xsl:with-param name="value">
					<xsl:value-of select="substring(., 1, 4)"/>
					<xsl:text>///</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			Notes:
			md-description -> N1
			md-abstract -> AB
		-->
		<xsl:for-each select="md-description|md-physical-extent">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">N1</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		<xsl:for-each select="md-abstract">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">AB</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			Journal information:
			md-journal-title -> JF
		-->
		<xsl:for-each select="md-journal-title">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">JF</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		
		<!--
			Article information.
			Distinguish two cases:
				a) we don’t have detailed volume/issue/pages information,
					(wrongly) map all we have to the volume field:
					md-journal-subpart -> VL
				b) we do have detailed volume/issue/pages information:
					md-volume-number -> VL
					md-issue-number -> IS
					md-pages-number -> SP/EP
					
		-->
		<xsl:choose>
			<xsl:when test="md-volume-number|md-pages-number">
				<xsl:for-each select="md-volume-number">
					<xsl:call-template name="RISLineMaker">
						<xsl:with-param name="key">VL</xsl:with-param>
						<xsl:with-param name="value" select="."/>
					</xsl:call-template>
				</xsl:for-each>

				<xsl:for-each select="md-issue-number">
					<xsl:call-template name="RISLineMaker">
						<xsl:with-param name="key">IS</xsl:with-param>
						<xsl:with-param name="value" select="."/>
					</xsl:call-template>
				</xsl:for-each>
				
				<!--
					If there is a hyphen - in md-pages-number, use it to split
					the field up and create SP and EP fields. Otherwise write 
					the whole field into SP.
				-->
				<xsl:choose>
					<xsl:when test="contains(md-pages-number, '-')">
						<xsl:call-template name="RISLineMaker">
							<xsl:with-param name="key">SP</xsl:with-param>
							<xsl:with-param name="value" select="substring-before(md-pages-number, '-')"/>
						</xsl:call-template>
						<xsl:call-template name="RISLineMaker">
							<xsl:with-param name="key">EP</xsl:with-param>
							<xsl:with-param name="value" select="substring-after(md-pages-number, '-')"/>
						</xsl:call-template>	
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="RISLineMaker">
							<xsl:with-param name="key">SP</xsl:with-param>
							<xsl:with-param name="value" select="md-pages-number"/>
						</xsl:call-template>	
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			
			<xsl:otherwise>
				<xsl:for-each select="md-journal-subpart">
					<xsl:call-template name="RISLineMaker">
						<xsl:with-param name="key">VL</xsl:with-param>
						<xsl:with-param name="value" select="."/>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
		
		
		<!--
			Publication information:
			md-publication-name -> PB
			md-publication-place -> CY
		-->
		<xsl:for-each select="md-publication-name">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">PB</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>

		<xsl:for-each select="md-publication-place">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">CY</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			ISBN/ISSN:
			md-isbn -> SN
			md-issn -> SN
			md-pissn -> SN
			md-eissn -> SN
		-->
		<xsl:for-each select="md-isbn|md-issn|md-pissn|md-eissn">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">SN</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			DOI: Map dx.doi.org URL to the URL field and to the 
			non-specified DO field which seems to occur in practice.
		-->
		<xsl:for-each select="md-doi">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">DO</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">UR</xsl:with-param>
				<xsl:with-param name="value">
					<xsl:text>http://dx.doi.org/</xsl:text>
					<xsl:value-of select="."/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>

		
		<!--
			Other links:
			md-electronic-url -> L2 (Full Text) if known full text
			                  -> UR (Web/URL) otherwise
		-->
		<xsl:for-each select="md-electronic-url">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">
					<xsl:choose>
						<xsl:when test="@fulltextfile='true'">L2</xsl:when>
						<xsl:otherwise>UR</xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!--
			Catalogue link:
			md-catalogue-url -> L3 (Related Records)
		-->
		<xsl:for-each select="md-catalogue-url">
			<xsl:call-template name="RISLineMaker">
				<xsl:with-param name="key">L3</xsl:with-param>
				<xsl:with-param name="value" select="."/>
			</xsl:call-template>
		</xsl:for-each>
		
		
		<!-- RIS needs to end with a EndRecord field -->
		<xsl:call-template name="RISLineMaker">
			<xsl:with-param name="key">ER</xsl:with-param>
			<xsl:with-param name="value"/>
		</xsl:call-template>	
		<xsl:text>&#13;&#10;</xsl:text>
	</xsl:template>


	<xsl:template name="RISLineMaker">
		<xsl:param name="key"/>
		<xsl:param name="value"/>
		
		<xsl:value-of select="$key"/>
		<xsl:text>  - </xsl:text>
		<xsl:value-of select="$value"/>
		<xsl:text>&#13;&#10;</xsl:text>
	</xsl:template>

</xsl:stylesheet>
