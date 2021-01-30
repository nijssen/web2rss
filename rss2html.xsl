<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output method="xml" indent="yes" encoding="utf-8" />
    
    <xsl:template match="/rss/channel">
        <p>
            <b>Feed title: </b> <xsl:value-of select="title" /><br />
            <b>Feed link: </b> <a href="{link}" target="_blank"><xsl:value-of select="link" /></a><br />
        </p>
        
        <xsl:apply-templates select="item" />
    </xsl:template>
    
    <xsl:template match="item">
        <div style="border: 1px solid black; padding: 1em; margin-bottom: 2em;">
            <h1><a href="{link}" target="_blank"><xsl:value-of select="title" /></a></h1>
            <p>
                <b>link: </b> <a href="{link}" target="_blank"><xsl:value-of select="link" /></a><br />
                <b>pubDate: </b> <xsl:value-of select="pubDate" /><br />
                <b>guid: </b> <abbr title="Make sure this doesn't change every refresh!"><xsl:value-of select="guid" /></abbr><br />
            </p>
            <hr />
            <xsl:value-of select="description" disable-output-escaping="yes" />
        </div>
    </xsl:template>
    

</xsl:stylesheet>