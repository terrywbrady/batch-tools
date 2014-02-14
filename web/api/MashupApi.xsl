<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:mets="http://www.loc.gov/METS/" xmlns:xlink="http://www.w3.org/TR/xlink/"  version="1.0">
<xsl:output method="xml"/>
  <xsl:param name="proto">https</xsl:param>
  <xsl:param name="host"></xsl:param>
  <xsl:param name="from">0</xsl:param>
  <xsl:param name="rows">0</xsl:param>
  <xsl:param name="search">0</xsl:param>
  <xsl:template match="/response">
    <results>
      <xsl:attribute name="numFound">
        <xsl:value-of select="result/@numFound"/>
      </xsl:attribute>
      <xsl:attribute name="from">
        <xsl:value-of select="$from"/>
      </xsl:attribute>
      <xsl:attribute name="rows">
        <xsl:value-of select="$rows"/>
      </xsl:attribute>
      <xsl:attribute name="search">
        <xsl:value-of select="$search"/>
      </xsl:attribute>
      <xsl:apply-templates select="result/doc"/>
    </results>

  </xsl:template>

  <xsl:template match="doc">
    <xsl:variable name="handle" select="str[@name='handle']/text()"/>
    <xsl:variable name="mets" select="concat($proto,'://',$host,'/metadata/handle/',$handle,'/mets.xml')"/>
    <xsl:variable name="metsdoc" select="document($mets)"/>
    <result>
      <xsl:attribute name="index">
        <xsl:number value="number($from)+position()-1"/>
      </xsl:attribute>      
      <handle>
        <xsl:value-of select="$handle"/>
      </handle>
      <title>
        <xsl:value-of select="arr[@name='dc.title']/str"/>
      </title>
      <xsl:choose>
        <xsl:when test="arr[@name='dc.creator.en_US' or @name='dc.contributor.author.en_US']">
          <xsl:for-each select="arr[@name='dc.creator.en_US' or @name='dc.contributor.author.en_US']/str">
            <creator>
              <xsl:value-of select="."/>
            </creator>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="arr[@name='dc.creator' or @name='dc.contributor.author']/str">
            <creator>
              <xsl:value-of select="."/>
            </creator>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:choose>
        <xsl:when test="arr[@name='dc.description.en_US']">
          <xsl:for-each select="arr[@name='dc.description.en_US']/str">
            <description>
              <xsl:value-of select="."/>
            </description>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="arr[@name='dc.description']/str">
            <description>
              <xsl:value-of select="."/>
            </description>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:choose>
        <xsl:when test="arr[@name='dc.subject.en_US']">
          <xsl:for-each select="arr[@name='dc.subject.en_US']/str">
            <subject>
              <xsl:value-of select="."/>
            </subject>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="arr[@name='dc.subject']/str">
            <subject>
              <xsl:value-of select="."/>
            </subject>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
      <date-created>
        <xsl:choose>
          <xsl:when test="arr[@name='dc.date.created'][1]/str[text()='No Date']">
            <xsl:value-of select="arr[@name='dc.date.created'][1]/str"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="substring(arr[@name='dc.date.created'][1]/str,1,4)"/>
          </xsl:otherwise>
        </xsl:choose>
      </date-created>
      <item-url>
        <xsl:value-of select="concat($proto,'://',$host,'/handle/',$handle)"/>
      </item-url>
      <permalink>
        <xsl:value-of select="arr[@name='dc.identifier.uri'][1]/str"/>
      </permalink>
      <xsl:if test="$metsdoc//mets:fileGrp[@USE='THUMBNAIL']/mets:file/mets:FLocat/@xlink:href">
      <thumbnail-url>
        <xsl:value-of select="concat($proto,'://',$host,$metsdoc//mets:fileGrp[@USE='THUMBNAIL']/mets:file/mets:FLocat/@xlink:href)"/>
      </thumbnail-url>
      </xsl:if>
    </result>
  </xsl:template>

</xsl:stylesheet>