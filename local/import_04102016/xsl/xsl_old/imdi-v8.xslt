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
                <field name="name">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                </field>
                <!-- Corpus Title -->
                <field name="title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                </field>
                <!-- Corpus description -->
		<xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Description">
		    <xsl:variable name="fieldName" select="./@Name"/>
		    <xsl:if test="not(string($fieldName))">
			<field name="description">
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
                <field name="coverImage">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CoverImage']"/>
                </field>
		<!--Depositor-->
                <field name="depositor">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Name"/>
                </field>
                <field name="depositorImage">
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
                <!-- Session (Bundle) Name -->
                <field name="name">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                </field>
                <!-- Session Title -->
                <field name="title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Title"/>
                </field>
                <!-- Session Date -->
                <field name="date">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Date"/>
                </field>
                <!-- Session Date -->
                <field name="description">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Description"/>
                </field>
                <!-- Access protocol -->
                <field name="accessProtocol">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Access/imdi:Availability"/>
                </field>
                <!-- Language -->
		<xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language">
                	<field name="languageName">
				<xsl:value-of select="./imdi:Name"/>
                	</field>
                </xsl:for-each> 	
                <!-- Resource file path  -->
                <field name="resourceLink">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:ResourceLink"/>
                </field>
                <!-- Resource file type  -->
                <field name="resourceType">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Type"/>
                </field>
           </doc>
	</xsl:element>        
    </xsl:template>
</xsl:stylesheet>
