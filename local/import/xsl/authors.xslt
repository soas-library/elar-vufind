<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:imdi="http://www.mpi.nl/IMDI/Schema/IMDI">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>

    <!-- Variables -->
    <xsl:variable name="empty_string"/>

    <!-- Only process the schema I want to deal with -->         
    <xsl:template match="imdi:METATRANSCRIPT">
        <xsl:apply-templates select="imdi:Session"/>
        <xsl:apply-templates select="imdi:Corpus/imdi:MDGroup/imdi:Actors" />
        <xsl:apply-templates select="imdi:Corpus" />
    </xsl:template>

    <!-- Process a deposit -->
    <xsl:template match="imdi:Actor">
        <xsl:element name="add">
            <doc>
                <!-- ID -->
                <xsl:for-each select="imdi:Code">
                        <field name="id">
                                <xsl:value-of select="."/>
                        </field>
                </xsl:for-each>

                <!-- RECORDTYPE -->
                <field name="record_type">Author</field>
                <!-- FORMAT -->
                <field name="source">Author</field>

                <!--Depositor-->
                <xsl:for-each select="imdi:Name">
                        <field name="heading">
                                <xsl:value-of select="."/>
                        </field>
                </xsl:for-each>
                <!--Depositor Nationality-->
                <xsl:for-each select="imdi:Keys/imdi:Key[@Name='Nationality']">
                        <xsl:if test="string-length() > 0">
                                <field name="nationality">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:if>
                </xsl:for-each>
                <!--Depositor Affiliation-->
                <xsl:for-each select="imdi:Keys/imdi:Key[@Name='Affiliation']">
                        <xsl:if test="string-length() > 0">
                                <field name="affiliation">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:if>
                </xsl:for-each>
                <!--Depositor URLS-->
                <xsl:for-each select="imdi:Keys/imdi:Key[@Name='Link']">
                        <xsl:if test="string-length() > 0">
                                <field name="url">
                                        <xsl:value-of select="."/>
                                </field>
                        </xsl:if>
                </xsl:for-each>
                <!--Depositor Full Name-->
                <xsl:for-each select="imdi:FullName">
                        <field name="full_name">
                                <xsl:value-of select="."/>
                        </field>
                </xsl:for-each>

      		<!--Depositor Image-->

                <xsl:for-each select="imdi:Keys/imdi:Key[@Name='Picture']">
                        <xsl:if test="string-length() > 0">
                                <field name="image">
                                        <xsl:value-of select="php:function('Soas::resourceLink',  string(.))"/>
                                </field>
                        </xsl:if>
                </xsl:for-each> 

                <!-- FULLRECORD -->
                <field name="fullrecord">
                    <xsl:value-of select="normalize-space(string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors))"/>
                </field>


                <!-- ALLFIELDS -->
                <field name="allfields">
                    <xsl:value-of select="normalize-space(string(//imdi:METATRANSCRIPT/imdi:Corpus/imdi:MDGroup/imdi:Actors))"/>
                </field>



            </doc>
        </xsl:element>        
    </xsl:template>

    <xsl:template match="imdi:Session">
        <xsl:element name="add">
            <doc>
                <field name="id">1111111</field>
            </doc>
        </xsl:element>        
    </xsl:template>

    <xsl:template match="imdi:Corpus">
        <xsl:element name="add">
            <doc>
                <field name="id">1111111</field>
            </doc>
        </xsl:element>
    </xsl:template>




</xsl:stylesheet>

