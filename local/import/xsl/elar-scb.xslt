<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:imdi="http://www.mpi.nl/IMDI/Schema/IMDI">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>

    <!-- Variables -->
    <xsl:variable name="empty_string"/>

    <!-- Only process the schema I want to deal with -->         
    <xsl:template match="imdi:METATRANSCRIPT">
        <xsl:apply-templates select="imdi:Corpus" />
        <xsl:apply-templates select="imdi:Session/imdi:Resources"/>

    </xsl:template>

    <!-- Process a deposit -->
    <xsl:variable name="id" select="php:function('Soas::changeId', string(//identifier))"/>
    <xsl:template match="imdi:Corpus">

    <xsl:variable name="hierarchy_top_id" select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>

    <xsl:choose>
    <xsl:when test="not(string($id) != string($hierarchy_top_id))">

        <xsl:element name="add">
            <doc>
                <!-- SetSpec -->
                <xsl:for-each select="//imdi:METATRANSCRIPT/setSpec">
                    <field name="setSpec"><xsl:value-of select="php:function('Soas::changeSpec', normalize-space())"/><xsl:value-of select="php:function('Soas::changeId', string(//identifier))"/>#</field>
                 </xsl:for-each>
                <!-- Record ID and Hierarchy info -->
                <!-- RECORDTYPE -->
                <field name="recordtype">corpus</field>
                <!-- FORMAT -->
                <field name="format">Deposit</field>
                <!-- FORMAT_SORT -->
                <field name="format_sort">Deposit</field>
                <!-- HIERARCHYTYPE -->
                <field name="hierarchytype">Default</field>
                <!-- ID -->
                <field name="id">
                    <xsl:value-of select="php:function('Soas::changeId', string(//identifier))"/>
                </field>
                <!-- CORPUSID -->
                <field name="corpusid">
                    <xsl:value-of select="string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId'])"/> 
                </field>
                <!-- hierarchy_top_id used to enable simple linking to patent corpor record -->
                <!-- BORRAR <field name="hierarchy_top_id">
                        <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>-->
                <field name="hierarchy_top_id">
                        <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>
                <!-- HIERARCHY_TOP_TITLE -->
                <field name="hierarchy_top_title">
                        <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>
                <!-- ID to facilitate simple hierarchy -->
                <field name="is_hierarchy_id">
                        <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>
                <!-- IS_HIERARCHY_TITLE -->
                <field name="is_hierarchy_title">
                        <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>
                <!-- Body info -->
                <!-- Corpus (Deposit) Name -->
                <!-- DEPOSIT_CONTINENT -->
                <field name="deposit_continent">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Location/imdi:Continent"/>
                </field>
                <field name="deposit_name">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                </field>
                <!-- SCB Longitude -->
                <field name="deposit_longitude">
                    <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Longitude']"/>
                </field>
                <!-- SCB Longitude -->
                <!-- SCB Latitude -->
                <field name="deposit_latitude">
                    <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Latitude']"/>
                </field>
                <!-- SCB Latitude -->
                <!-- SCB Deposit Status -->
                <field name="deposit_status">
                    <xsl:value-of select="php:function('Soas::statusCode',  string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Deposit_Status']))"/>
                    <!--<xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='StatusInfo_Status']"/>-->
                </field>
                <field name="deposit_status_string">
                    <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Deposit_Status']"/>
                </field>
                <!-- SCB Deposit Status -->
                <!-- SCB Deposit Change Date -->
                <field name="deposit_change_date">
                    <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='StatusInfo_ChangeDate']"/>
                </field>
                <!-- FUNDING BODY -->
                <xsl:variable name="funding_body_original" select="php:function('Soas::getFundingBody', string(//imdi:METATRANSCRIPT/setSpec))"/>
                <xsl:if test="string-length($funding_body_original) > 0">
                  <xsl:if test="contains($funding_body_original,',')">
                     <xsl:for-each select="php:function('VuFind::explode', ',', string($funding_body_original))/part">
                      <xsl:if test="string-length() > 0">
                        <field name="funding_body"><xsl:value-of select="normalize-space(string(.))"/></field>
                      </xsl:if>
                    </xsl:for-each>
                  </xsl:if>
                  <xsl:if test="not(contains($funding_body_original,','))">
                        <field name="funding_body"><xsl:value-of select="normalize-space(string($funding_body_original))"/></field>
                  </xsl:if>
                </xsl:if>
                <!--<field name="funding_body">
                    <xsl:value-of select="php:function('Soas::getFundingBody', string(//imdi:METATRANSCRIPT/setSpec))"/>
                </field>-->

                <!-- FUNDING BODY -->




                <!-- PROJECT ID -->
                <xsl:variable name="project_original" select="php:function('Soas::getProjectId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                <xsl:if test="string-length($project_original) > 0">
                  <xsl:if test="contains($project_original,',')">
                     <xsl:for-each select="php:function('VuFind::explode', ',', string($project_original))/part">
                      <xsl:if test="string-length() > 0">
                        <field name="project_id"><xsl:value-of select="normalize-space(string(.))"/></field>
                      </xsl:if>
                    </xsl:for-each>
                  </xsl:if>
                  <xsl:if test="not(contains($project_original,','))">
                        <field name="project_id"><xsl:value-of select="normalize-space(string($project_original))"/></field>
                  </xsl:if>

                </xsl:if>
                <!-- PROJECT ID -->
                <!-- PROJECT ID_NUMBER -->
                <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Project/imdi:Id">
                    <xsl:if test="string-length(.)>0">
                        <field name="project_id_number">
                             <xsl:value-of select="."/>
                        </field>
                    </xsl:if>
                </xsl:for-each>
                <!-- PROJECT ID_NUMBER -->

                <!-- FIRST AND LAST INDEXED (CORPUS) -->
                <field name="first_indexed">
                    <xsl:value-of select="php:function('VuFind::getFirstIndexed', 'biblio', string($id), string('now'))"/>
                </field>
                <field name="last_indexed">
                    <xsl:value-of select="php:function('VuFind::getLastIndexed', 'biblio', string($id), string('now'))"/>
                </field>
                <!--<field name="first_indexed">
                    <xsl:value-of select="php:function('Soas::getFirstIndexed', 'biblio', string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']), string('now'))"/> 
                </field>
                <field name="last_indexed">
                    <xsl:value-of select="php:function('Soas::getLastIndexed', 'biblio', string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']), string('now'))"/>
                </field>-->
                <!-- SCB Deposit Change Date -->
                <!-- Title -->
                <!--<field name="title">
                        <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                </field>-->

                        <xsl:if test="string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0">
                            <field name="title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0)">
                            <field name="title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                            </field>
                        </xsl:if>


                <!-- Corpus Title -->
                        <xsl:if test="string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0">
                            <field name="deposit_title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0)">
                            <field name="deposit_title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0">
                            <field name="clean_title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Title)>0)">
                            <field name="clean_title">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Name"/>
                            </field>
                        </xsl:if>

                        <!-- Corpus description -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:Description">
                            <xsl:variable name="fieldName" select="./@Name"/>
                            <xsl:if test="not(string($fieldName))">
                                <field name="deposit_description">
                                    <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                    <xsl:value-of select="."/>
                                    <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                </field>
                            </xsl:if>
                            <xsl:if test="string($fieldName)">
                                <field name="{$fieldName}">
                                    <xsl:choose>
                                    <xsl:when test="$fieldName='summary_of_deposit'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Summary of deposit')">
                                        <xsl:value-of select="substring-after(php:function('Soas::removeLink',  string(.)),'Summary of deposit')"/>
                                        <!--<xsl:value-of select="substring-after(.,'Summary of deposit')"/>-->
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <!--<xsl:value-of select="."/>-->
                                        <xsl:value-of select="php:function('Soas::removeLink',  string(.))"/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='group_represented'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Group represented')">
                                        <xsl:value-of select="substring-after(.,'Group represented')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='deposit_contents'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Deposit contents')">
                                        <!--<xsl:value-of select="substring-after(.,'Deposit contents')"/>-->
                                        <xsl:value-of select="php:function('Soas::changeDepositLink',  string(substring-after(.,'Deposit contents')))"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='deposit_history'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Deposit history')">
                                        <xsl:value-of select="substring-after(.,'Deposit history')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='short_description'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Short description')">
                                        <xsl:value-of select="substring-after(.,'Short description')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='special_characteristics'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Special Characteristics')">
                                        <xsl:value-of select="substring-after(.,'Special Characteristics')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='history_of_deposit'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'History of deposit')">
                                        <xsl:value-of select="substring-after(.,'History of deposit')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='other_information'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Other information')">
                                        <xsl:value-of select="substring-after(.,'Other information')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="$fieldName='acknowledgement'">
                                       <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <xsl:choose>
                                        <xsl:when test="starts-with(., 'Acknowledgement and citation')">
                                        <xsl:value-of select="substring-after(.,'Acknowledgement and citation')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:choose>
                                            <xsl:when test="starts-with(., 'Acknowledgement')">
                                            <xsl:value-of select="substring-after(.,'Acknowledgement')"/>
                                            </xsl:when>
                                            <xsl:otherwise>
                                            <xsl:value-of select="."/>
                                            </xsl:otherwise>
                                            </xsl:choose>
                                        </xsl:otherwise>
                                        </xsl:choose>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
                                        <!-- <xsl:value-of select="."/> -->
                                        <!-- SCB Change links por swf files -->
                                        <xsl:value-of select="php:function('Soas::changeDepositLink',  string(.))"/>
                                        <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
                                     </xsl:otherwise>
                                    </xsl:choose>
                                </field>
                            </xsl:if>
                        </xsl:for-each>
                        <!-- Deposit language (but associated to bundle language_name) -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Name">
                                <field name="bundle_language_name">
                                     <xsl:value-of select="."/>
                                </field>          
                        </xsl:for-each>           
                        <!-- Deposit Language (string) (but associated to bundle language_name) -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Name">
                                <field name="bundle_language_name_string">
                                     <xsl:value-of select="."/>
                                </field>          
                        </xsl:for-each>           
                        <!-- Corpus Cover image -->
                        <field name="deposit_cover_image">
                                <xsl:value-of select="php:function('Soas::changeDepositLink',  string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CoverImage']))"/>
                        </field>
                        <!--Corpus Cover image-->
                        
                        <!-- Corpus Cover image -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CoverImage']">
                                <xsl:if test="string-length() > 0">
                                        <field name="depositpage_image">
                                                <xsl:value-of select="php:function('Soas::resourceLink',  string(.))"/>
                                                <!--<xsl:value-of select="."/>-->
                                        </field>
                                </xsl:if>
                        </xsl:for-each>
                        <!-- Corpus Cover image -->
                        
                        <!-- Podcast -->
                        <field name="depositpage_podcast">
                                <xsl:value-of select="php:function('Soas::resourceLink',  string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Podcast']))"/>
                        </field>
                        <!-- Podcast -->
                        
                        <!-- Showreel -->
                        <field name="depositpage_showreel">
                                <xsl:value-of select="php:function('Soas::resourceLink',  string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='Showreel']))"/>
                        </field>
                        <!-- Showreel -->
                        
                        <!-- Depositor image -->

                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor">
                            <field name="depositor_picture"><xsl:for-each select="imdi:Keys/imdi:Key[@Name='Picture']"><xsl:value-of select="php:function('Soas::resourceLink', string(.))"/></xsl:for-each></field>
                        </xsl:for-each>

                        <!-- Depositor image -->
                        
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Name">
                                <field name="depositor">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Code">
                                <field name="depositor_id">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                        <!--Depositor Nationality-->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor">
                            <field name="depositor_nationality"><xsl:for-each select="imdi:Keys/imdi:Key[@Name='Nationality']"><xsl:value-of select="."/></xsl:for-each></field>
                        </xsl:for-each>

                        <!--Depositor Affiliation-->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor">
                            <field name="depositor_affiliation"><xsl:for-each select="imdi:Keys/imdi:Key[@Name='Affiliation']"><xsl:value-of select="."/></xsl:for-each></field>
                        </xsl:for-each>

                        <!-- SCB Author -->
                        <field name="author">
                                <xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Name"/>
                        </field>
                        <!-- SCB Author -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Keys/imdi:Key[@Name='Picture']">
                                <xsl:if test="string-length() > 0">
                                        <field name="depositor_image">
                                                <xsl:value-of select="php:function('Soas::changeDepositLink',  string(.))"/>
                                                <!--<xsl:value-of select="."/>-->
                                        </field>
                                </xsl:if>
                        </xsl:for-each>
                        <!-- SCB Language Information -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Description">
                                <field name="language_information">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>

                        <!-- SCB Deposit language -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Name">
                                <field name="deposit_language_name">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
			<!-- SCB Deposit language description -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Id">
                                <field name="deposit_language_name_iso">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
			<!-- SCB Deposit country -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Location/imdi:Country">
                                <xsl:if test="string-length() > 0">
                                        <field name="deposit_country_name">
                                                <xsl:value-of select="."/>
                                        </field>
                                </xsl:if>
                        </xsl:for-each>
                        <!-- SCB Country (Deposit and bundle) -->
                        <xsl:for-each select="//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Location/imdi:Country">
                                <xsl:if test="string-length() > 0">
                                        <field name="country_name">
                                                <xsl:value-of select="."/>
                                        </field>
                                </xsl:if>
                        </xsl:for-each>



                   </doc>
                </xsl:element>

    </xsl:when>
    <xsl:otherwise>
        <doc><field name="id">9999</field></doc>
    </xsl:otherwise>
    </xsl:choose>
    </xsl:template>

            <!-- Process WrittenResources -->
            <xsl:template match="imdi:Session/imdi:Resources">
                 <xsl:element name="add">
                    <doc>

                        <!-- SCB MULTIPLE PROTOCOL -->
                        <xsl:for-each select="php:function('VuFind::explode', '/', string(php:function('Soas::getAccessLevel', string(//identifier))))/part">
                            <field name="resource_access_protocol"><xsl:value-of select="."/></field>
                        </xsl:for-each>

                        <!-- SCB MULTIPLE Resource filenames -->
                        <xsl:for-each select="php:function('VuFind::explode', '/', string(php:function('Soas::getResourceName', string(//identifier))))/part">
                            <field name="resource_filename"><xsl:value-of select="."/></field>
                        </xsl:for-each>

                        <!-- SetSpec -->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/setSpec">
                            <field name="setSpec"><xsl:value-of select="php:function('Soas::changeSpec', normalize-space())"/><xsl:value-of select="php:function('Soas::changeId', string(//identifier))"/>#</field>
                        </xsl:for-each>
                        <!-- Record ID and Hierarchy info -->
                        <!-- RECORDTYPE -->
                        <field name="recordtype">resource</field>
                        <!-- FORMAT -->
                        <field name="format">Bundle</field>
                        <!-- FORMAT_SORT -->
                        <field name="format_sort">Bundle</field>
                        <!-- HIERARCHYTYPE -->
                        <field name="hierarchytype">Default</field>
                        <!-- ID -->
                        <field name="id">
                                <!--<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>-<xsl:value-of select="//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='SessionId']"/>-->
                                <!--<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>-<xsl:value-of select="//imdi:METATRANSCRIPT/identifier"/>-->
                                <xsl:value-of select="php:function('Soas::changeId', string(//identifier))"/>
                        </field>
                        <!-- FIRST AND LAST INDEXED (SESSION) -->
                        <field name="first_indexed">
                            <xsl:value-of select="php:function('VuFind::getFirstIndexed', 'biblio', string($id), string('now'))"/>
                        </field>
                        <field name="last_indexed">
                            <xsl:value-of select="php:function('VuFind::getLastIndexed', 'biblio', string($id), string('now'))"/>
                        </field>
                        <!--<field name="first_indexed">
                            <xsl:value-of select="php:function('Soas::getFirstIndexed', 'biblio', string(//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='SessionId']), string('now'))"/>
                        </field>
                        <field name="last_indexed">
                            <xsl:value-of select="php:function('Soas::getLastIndexed', 'biblio', string(//imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='SessionId']), string('now'))"/>
                        </field>-->

                        <!-- hierarchy_top_id used to enable simple linking to patent corpor record -->
                        <field name="hierarchy_top_id">
                            <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                        </field>

                        <!-- FUNDING BODY -->
                        <xsl:variable name="funding_body_original" select="php:function('Soas::getFundingBody', string(//imdi:METATRANSCRIPT/setSpec))"/>
                        <xsl:if test="string-length($funding_body_original) > 0">
                          <xsl:if test="contains($funding_body_original,',')">
                             <xsl:for-each select="php:function('VuFind::explode', ',', string($funding_body_original))/part">
                              <xsl:if test="string-length() > 0">
                                <field name="funding_body"><xsl:value-of select="normalize-space(string(.))"/></field>
                              </xsl:if>
                            </xsl:for-each>
                          </xsl:if>
                          <xsl:if test="not(contains($funding_body_original,','))">
                                <field name="funding_body"><xsl:value-of select="normalize-space(string($funding_body_original))"/></field>
                          </xsl:if>
                        </xsl:if>


                        <!-- hierarchy_parent_id used to enable simple linking to patent corpor record -->
                        <field name="hierarchy_parent_id">
                            <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                        </field>
                        <!-- container_title used to enable simple linking to patent corpor record -->
                        <field name="container_title">
                            <xsl:value-of select="php:function('Soas::getParentId', string(//imdi:METATRANSCRIPT/setSpec))"/>
                        </field>
                        <!-- Bundle Name -->
                        <field name="bundle_name">
                                <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                                <!--<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='SessionId']"/>-->
                        </field>
                        <!-- Bundle Name -->
                        <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0">
                            <field name="title">
                                <!-- <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title"/> -->
                                <xsl:variable name="firstChar" select="substring(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,1,1)"/>
                                <xsl:value-of select="translate($firstChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/><xsl:value-of select="substring-after(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,$firstChar)"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0)">
                            <field name="title">
                                <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                            </field>
                        </xsl:if>
                        <!-- Bundle Title -->
                        <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0">
                            <field name="bundle_title">
                                <!-- <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title"/> -->
                                <xsl:variable name="firstChar" select="substring(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,1,1)"/>
                                <xsl:value-of select="translate($firstChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/><xsl:value-of select="substring-after(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,$firstChar)"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0)">
                            <field name="bundle_title">
                                <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                            </field>
                        </xsl:if>

                        <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0">
                            <field name="clean_title">
                                <!-- <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title"/> -->
                                <xsl:variable name="firstChar" select="substring(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,1,1)"/>
                                <xsl:value-of select="translate($firstChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/><xsl:value-of select="substring-after(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title,$firstChar)"/>
                            </field>
                        </xsl:if>
                        <xsl:if test="not(string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Title)>0)">
                            <field name="clean_title">
                                <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Name"/>
                            </field>
                        </xsl:if>

                <!-- Bundle Date Created-->
                <field name="bundle_date_created">
                        <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Date"/>
                </field>
                <!-- Bundle Location-->

                <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Continent) > 0">
                    <xsl:if test="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Continent!='Unspecified'">
                        <field name="bundle_location">
                            <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Continent"/>
                        </field>
                    </xsl:if>
                </xsl:if>

                <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Country) > 0">
                    <xsl:if test="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Country!='Unspecified'">
                        <field name="bundle_location">
                            <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Country"/>
                        </field>
                    </xsl:if>
                </xsl:if>


                <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Region) > 0">
                    <xsl:if test="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Region!='Unspecified'">
                        <field name="bundle_location">
                            <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Region"/>
                        </field>
                    </xsl:if>
                </xsl:if>


                <xsl:if test="string-length(ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address) > 0">
                    <xsl:if test="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address!='Unspecified'">
                        <field name="bundle_location">
                            <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address"/>
                        </field>
                    </xsl:if>
                </xsl:if>

                <!-- <field name="bundle_location">
                     <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Continent"/>
                </field>
                <field name="bundle_location">
                     <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Country"/>
                </field>
                <field name="bundle_location">
                     <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Region"/>
                </field>
                <field name="bundle_location">
                     <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address"/>
                </field>
                <field name="bundle_location_string">
                     <xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Address"/>
                </field>-->

                <!-- SCB Country (Deposit and bundle) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Location/imdi:Country">
                        <xsl:if test="string-length() > 0">
                                <field name="country_name">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:if>
                </xsl:for-each>

                <!-- Bundle Description -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Description">
                <field name="bundle_description">
                        <xsl:value-of select="."/>
                </field>
                </xsl:for-each>
                <!-- Bundle language - best to use this rather than custom keys for resources in SOAS imdi as not all deposits in LAT come from ELDP Profile sheets -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Name">
                        <field name="bundle_language_name">
                             <xsl:value-of select="."/>
                        </field>
                </xsl:for-each>
                <!-- Bundle Language (string) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language/imdi:Name">
                        <field name="bundle_language_name_string">
                             <xsl:value-of select="."/>
                        </field>
                </xsl:for-each>
                <!-- Bundle Genre -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Genre']">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_genre">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- Bundle Genre (string) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Genre']">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_genre_string">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>

                <!-- Bundle Genre (ELDP Missing data) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Genre">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_genre">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- Bundle Genre (string) (ELDP Missing data) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Genre">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_genre_string">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- Bundle Tag -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Tag']">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_tag">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- Bundle Tag (string) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Tag']">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_tag_string">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>

                <!-- Bundle Tag (ELDP Missing data) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Tag">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_tag">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- Bundle Tag (string) (ELDP Missing data) -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Tag">
                        <xsl:if test="string-length() > 0">
                            <field name="bundle_tag_string">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
		
		<!-- Participants (Actors) - as with language imdi groups by bundle not resource so using generic imdi rather than custom keys for same reasoning as language  -->
                <!-- NB imdi:FullName does not always contain a value sometimes no name provided in ELDP Profile sheets so used perso id but only in name field -->
                <!--<xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Name">
                     <xsl:if test="string-length() > 0">
                     <field name="bundle_participant">
                           <xsl:value-of select="."/>
                     </field>
                     </xsl:if>
                     <xsl:if test="string-length() = 0">
                         <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:FullName">
                             <xsl:if test="string-length() > 0">
                                 <field name="bundle_participant">
                                     <xsl:value-of select="."/>
                                 </field>
                             </xsl:if>
                         </xsl:for-each>
                     </xsl:if>
                </xsl:for-each>-->

                <!-- Bundle Keyword (ELDP Missing data)-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Topic">
                                <field name="resource_keyword">
                                     <xsl:value-of select="."/>
                                </field>
                                <field name="resource_keyword_string">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- SCB RESOURCE TAGS-->


                <!-- Participants (Actors) - as with language imdi groups by bundle not resource so using generic imdi rather than custom keys for same reasoning as language  -->
                <!-- NB imdi:FullName does not always contain a value sometimes no name provided in ELDP Profile sheets so used perso id but only in name field -->

                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor">
                     <xsl:choose>
                     <xsl:when test="string-length(imdi:FullName) > 0">
                     <field name="bundle_participant_name">
                           <xsl:value-of select="imdi:FullName"/>
                     </field>
                     <field name="bundle_participant">
                           <xsl:value-of select="imdi:FullName"/>
                     </field>
                     </xsl:when>
                     <xsl:otherwise>
                     <field name="bundle_participant_name">
                           <xsl:value-of select="imdi:Name"/>
                     </field>
                     <field name="bundle_participant">
                           <xsl:value-of select="imdi:Name"/>
                     </field>
                     </xsl:otherwise>
                     </xsl:choose>
                </xsl:for-each>

                <!--<xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor">
                     <xsl:choose>
                     <xsl:when test="string-length(imdi:Name) > 0">
                     <field name="bundle_participant_name">
                           <xsl:value-of select="imdi:Name"/>
                     </field>
                     <field name="bundle_participant">
                           <xsl:value-of select="imdi:Name"/>
                     </field>
                     </xsl:when>
                     <xsl:otherwise>
                     <field name="bundle_participant_name">
                           <xsl:value-of select="imdi:FullName"/>
                     </field>
                     <field name="bundle_participant">
                           <xsl:value-of select="imdi:FullName"/>
                     </field>
                     </xsl:otherwise>
                     </xsl:choose>
                </xsl:for-each>-->


                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Actors/imdi:Actor/imdi:Role">
                     <field name="bundle_participant_role">
                           <xsl:value-of select="."/>
                     </field>
                </xsl:for-each>

                <!-- SCB MULTIPLE RESOURCES-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile">
                                <field name="resource_link">
                                     ../resources/<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>/<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>/<xsl:value-of select="php:function('Soas::changeResourceLink',  string(imdi:ResourceLink))"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB MULTIPLE RESOURCES-->
                <!-- SCB Test -->
                        <!--<xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Access">
                                <field name="prueba">
                                     <xsl:value-of select="imdi:Availability"/>
                                </field>
                        </xsl:for-each>-->
                <!-- SCB RESOURCE TYPE-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile">
                                <field name="resource_type">
                                     <xsl:value-of select="php:function('Soas::changeFormat', normalize-space(imdi:Type))"/>
                                </field>
                                <field name="resource_type_string">
                                     <xsl:value-of select="php:function('Soas::changeFormat', normalize-space(imdi:Type))"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB RESOURCE TYPE-->
                
                <!-- Bundle Keywords-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Keyword']">
                                <field name="bundle_keywords">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- Bundle Keywords (string)-->
                
                <!-- SCB RESOURCE KEYWORDS-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Keys/imdi:Key[@Name='Topic']">
                                <field name="resource_keyword">
                                     <xsl:value-of select="."/>
                                </field>
                                <field name="resource_keyword_string">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- SCB RESOURCE KEYWORDS-->

                <!-- SCB RESOURCE KEYWORDS-->
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Keys/imdi:Key[@Name='Keyword']">
                                <field name="resource_keyword">
                                     <xsl:value-of select="."/>
                                </field>
                                <field name="resource_keyword_string">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='Keyword']">
                                <field name="resource_keyword">
                                     <xsl:value-of select="."/>
                                </field>
                                <field name="resource_keyword_string">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- SCB RESOURCE KEYWORDS-->



                <!-- SCB WRITTEN MULTIPLE RESOURCES-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource">
                                <field name="resource_link">
                                     ../resources/<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>/<xsl:value-of select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Keys/imdi:Key[@Name='CorpusId']"/>/<xsl:value-of select="php:function('Soas::changeResourceLink',  string(imdi:ResourceLink))"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB WRITTEN MULTIPLE RESOURCES-->
                <!-- SCB Test -->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Access">
                                <field name="prueba">
                                     <xsl:value-of select="imdi:Availability"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB Test -->

                <!-- SCB WRITTEN RESOURCE TYPE-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource">
                                <field name="resource_type">
                                     <xsl:value-of select="php:function('Soas::changeFormat', normalize-space(imdi:Type))"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB WRITTEN RESOURCE TYPE-->
                <!-- Resource file type - will need work in source imdi - possible custom key field -->
                <!-- SCB WRITTEN RESOURCE TYPE STRING-->
                        <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource">
                                <field name="resource_type_string">
                                     <xsl:value-of select="php:function('Soas::changeFormat', normalize-space(imdi:Type))"/>
                                </field>
                        </xsl:for-each>
                <!-- SCB WRITTEN RESOURCE TYPE STRING-->
                
                
                <!-- SCB CONDITION OF ACCESS-->
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Access/imdi:Description[@Name='Conditions of Access']">
                                <field name="resource_condition_access">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Access/imdi:Description[@Name='Conditions of Access']">
                                <field name="resource_condition_access">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- SCB CONDITION OF ACCESS-->

		<!-- SCB RESTRICTIONS-->
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Access/imdi:Description[@Name='Restrictions']">
                                <field name="resource_restrictions">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                       <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Access/imdi:Description[@Name='Restrictions']">
                                <field name="resource_restrictions">
                                     <xsl:value-of select="."/>
                                </field>
                        </xsl:for-each>
                <!-- SCB RESTRICTIONS-->

                <!-- Resource Recording Equipment -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Keys/imdi:Key[@Name='RecordingEquipment']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_rec_equipment">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='RecordingEquipment']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_rec_equipment">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                



                <!-- Resource Recording Conditions -->
		<xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:RecordingConditions">
                        <xsl:if test="string-length() > 0">
                            <xsl:if test=".!='Unspecified'">
                                <field name="resource_rec_condition">
                                    <xsl:value-of select="."/>
                                </field>
                            </xsl:if>
                        </xsl:if>
                </xsl:for-each>
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:RecordingConditions">
                        <xsl:if test="string-length() > 0">
                            <xsl:if test=".!='Unspecified'">
                                <field name="resource_rec_condition">
                                    <xsl:value-of select="."/>
                                </field>
                            </xsl:if>
                        </xsl:if>
                </xsl:for-each>


                <!-- Resource Additional information -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Keys/imdi:Key[@Name='AdditionalInformationObject']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_additional_info">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='AdditionalInformationObject']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_additional_info">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>


                <!-- Resource Participant -->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Keys/imdi:Key[@Name='Person']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_participant">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='Person']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_participant">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>



                <!-- SCB WORKING LANGUAGE-->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:WrittenResource/imdi:Keys/imdi:Key[@Name='Language']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_language">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:Resources/imdi:MediaFile/imdi:Keys/imdi:Key[@Name='Language']">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_language">
                                <xsl:value-of select="."/>
                            </field>
                        </xsl:if>
                </xsl:for-each>
                <!-- SCB WORKING LANGUAGE-->


                <!-- SCB WORKING LANGUAGE NEW 25/01/2017-->
                <xsl:for-each select="ancestor::imdi:METATRANSCRIPT/imdi:Session/imdi:MDGroup/imdi:Content/imdi:Languages/imdi:Language">
                        <xsl:if test="string-length() > 0">
                            <field name="resource_language"><xsl:value-of select="imdi:Name"/> (<xsl:value-of select="imdi:Description"/>)</field>
                        </xsl:if>
                </xsl:for-each>

                <!-- SCB WORKING LANGUAGE-->





           </doc>
        </xsl:element>        
    </xsl:template>
</xsl:stylesheet>
