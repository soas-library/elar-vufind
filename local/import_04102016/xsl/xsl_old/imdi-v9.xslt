<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:imdi="http://www.mpi.nl/IMDI/Schema/IMDI">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>

    <!-- Variables -->
    <xsl:variable name="empty_string"/>

    <!-- Only process the schema I wan't to deal with --> 	
    <xsl:template match="imdi:METATRANSCRIPT">
	<xsl:apply-templates select="imdi:Corpus" />
	<xsl:apply-templates select="imdi:Session/imdi:Resources/imdi:MediaFile" />
    </xsl:template>

    <!-- Process a deposit -->
    <xsl:template match="imdi:Corpus">
        <xsl:element name="add">
            <doc>
                <!-- Record ID and Hierarchy info -->
                <!-- RECORDTYPE -->
                <field name="recordtype">Corpus</field>
                <!-- FORMAT -->
                <field name="format">Project</field>
                <!-- HIERARCHYTYPE -->
                <field name="hierarchytype">Default</field>
                <!-- ID -->
                <field name="id">
                        <xsl:value-of select="substring(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name,1,13)"/>
                </field>
                <!-- hierarchy_top_id used to enable simple linking to patent corpor record -->
                <field name="hierarchy_top_id">
                        <xsl:value-of select="substring(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name,1,13)"/>
                </field>
                <!-- HIERARCHY_TOP_TITLE -->
                <field name="hierarchy_top_title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                </field>
		<!-- ID to facilitate simple hierarchy -->
                <field name="is_hierarchy_id">
                        <xsl:value-of select="substring(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name,1,13)"/>
                </field>
                <!-- IS_HIERARCHY_TITLE -->
                <field name="is_hierarchy_title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                </field>

		<!-- Body info -->
		<!-- Corpus (Deposit) Name -->
                <field name="deposit_name">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                </field>
                <!-- Corpus Title -->
                <field name="deposit_title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                </field>
                <!-- Corpus description -->
		<xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Description">
		    <xsl:variable name="fieldName" select="./@Name"/>
		    <xsl:if test="not(string($fieldName))">
			<field name="deposit_description">
			    <xsl:value-of select="."/>
                        </field>
		    </xsl:if>
		    <xsl:if test="string($fieldName)">
                        <field name="{$fieldName}">
                            <xsl:value-of select="."/>
                        </field>
		    </xsl:if>
		</xsl:for-each>
		<!-- Corpus Cover image -->
                <field name="deposit_cover_image">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CoverImage']"/>
                </field>
		<!--Depositor-->
                <field name="depositor">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Name"/>
                </field>
                <field name="depositor_image">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Keys/imdi:Key/@filepath"/>
                </field>
	   </doc>
	</xsl:element>
    </xsl:template>
    <xsl:template match="imdi:Session/imdi:Resources/imdi:MediaFile">
         <xsl:element name="add">
            <doc>
                <!-- Record ID and Hierarchy info -->            
                <!-- RECORDTYPE -->
                <field name="recordtype">MediaFile</field>
                <!-- FORMAT -->
                <field name="format">Resource</field>                
                <!-- HIERARCHYTYPE -->
                <field name="hierarchytype">Default</field>
                <!-- ID -->
		<!-- Original - changing this as suspect it is best to generate individual child record for each resource which incorporates bundle info where required!
                <field name="id">
                           <xsl:value-of select="//identifier"/>
                </field>
                -->
                <field name="id">
                	<xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>-<xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/@ResourceId"/>
                </field>

                <!-- hierarchy_top_id used to enable simple linking to patent corpor record -->
                <field name="hierarchy_top_id">
                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>
                </field>
                <!-- hierarchy_parent_id used to enable simple linking to patent corpor record -->
                <field name="hierarchy_parent_id">
                           <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>
                </field>
                <!-- container_title used to enable simple linking to patent corpor record -->
                <field name="container_title">
                           <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>
                </field>
                <!-- Bundle Name -->
                <field name="bundle_name">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                </field>
                <!-- Bundle Title -->
                <field name="bundle_title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Title"/>
                </field>
                <!-- Bundle Date Created-->
                <field name="bundle_date_created">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Date"/>
                </field>
                <!-- Bundle Location-->
                <field name="bundle_location">
                     <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address"/>
                </field>
                <!-- Bundle Description -->
                <field name="bundle_description">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Description"/>
                </field>
                <!-- Bundle language - best to use this rather than custom keys for resources in SOAS imdi as not all deposits in LAT come from ELDP Profile sheets -->
                <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language">
                        <field name="bundle_language_name">
                             <xsl:value-of select="./imdi:Name"/>
                        </field>
                </xsl:for-each>
                <!-- Bundle Genre -->
                <field name="bundle_genre">
                       <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Genre"/>
                </field>
                <!-- Participants (Actors) - as with language imdi groups by bundle not resource so using generic imdi rather than custom keys for same reasoning as language  -->
		<!-- NB imdi:FullName does not always contain a value sometimes no name provided in ELDP Profile sheets so used perso id but only in name field -->
                <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor">
                     <field name="bundle_participant">
                           <xsl:value-of select="./imdi:Name"/>
                     </field>
                </xsl:for-each>
                <!-- Keywords - only present   -->
                <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='Keyword']">
                      <field name="resource_keyword">
                            <xsl:value-of select="."/>
                      </field>
                </xsl:for-each>
                <!-- Resource Access Protocol - need to split into individual values-->
                <field name="resource_access_protocol">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Access/imdi:Availability"/>
                </field>
                <!-- Resource file path  -->
                <field name="resource_link">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:ResourceLink"/>
                </field>
                <!-- Resource file type - will need work in source imdi - possible custom key field -->
                <field name="resource_type">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Type"/>
                </field>
           </doc>
	</xsl:element>        
    </xsl:template>
</xsl:stylesheet>
