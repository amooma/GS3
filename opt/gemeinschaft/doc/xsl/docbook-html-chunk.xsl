<?xml version="1.0"?>
<xsl:stylesheet	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exsl="http://exslt.org/common"
	xmlns:set="http://exslt.org/sets"
	xmlns="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="exsl set"
	version="1.0"
	>

<!--<![CDATA[
### Import the appropriate DocBook stylesheet (HTML, chunking):
]]>-->
<!--<![CDATA[
<xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh/xhtml/chunk.xsl" />
]]>-->
<xsl:import href="http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl" />
<!--<![CDATA[
The URI
"http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl"
is likely to be mapped to
"file:///usr/share/xml/docbook/stylesheet/nwalsh/xhtml/chunk.xsl"
by the local XML catalog.
]]>-->

<!--<![CDATA[
<xsl:output
	method="xml"
	encoding="UTF-8"
	indent="yes"
	/>
]]>-->


<!--<![CDATA[
### Set stylesheet parameters:
]]>-->

<xsl:param name="chunker.output.method" select="'xml'" />
<xsl:param name="chunker.output.indent" select="'no'" />
<!-- Do not indent the output. Otherwise those crappy templates would add whitespace in <pre> elements. -->
<xsl:param name="chunker.output.encoding" select="'UTF-8'" />
<xsl:param name="chunker.output.omit-xml-declaration" select="'yes'" />
<xsl:param name="chunker.output.media-type" select="'html'" />
<xsl:param name="chunker.output.doctype-public" select="'-//W3C//DTD XHTML 1.0 Transitional//EN'" />
<xsl:param name="chunker.output.doctype-system" select="'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'" />

<xsl:param name="use.id.as.filename" select="1" />

<xsl:param name="html.ext" select="'.html'" />

<xsl:param name="html.stylesheet" select="'css/style.css'" />

<xsl:param name="admon.graphics" select="1" />
<xsl:param name="admon.textlabel" select="1" />
<xsl:param name="admon.graphics.extension" select="'.gif'" />
<xsl:param name="admon.graphics.path" select="'dbkimg/'" />
<xsl:param name="admon.style"></xsl:param>

<xsl:param name="navig.graphics" select="1" />
<xsl:param name="navig.graphics.extension" select="'.gif'" />
<xsl:param name="navig.graphics.path" select="'dbkimg/'" />
<xsl:param name="navig.showtitles" select="1" />

<xsl:param name="callout.graphics" select="0" />
<xsl:param name="callout.graphics.extension" select="'.gif'" />
<xsl:param name="callout.graphics.path" select="'dbkimg/'" />
<xsl:param name="callout.graphics.number.limit" select="15" />
<xsl:param name="callout.unicode" select="0" />

<xsl:param name="use.role.for.mediaobject" select="1" />
<xsl:param name="preferred.mediaobject.role" select="'html'" />

<xsl:param name="chunk.first.sections" select="1" />
<xsl:param name="chunk.section.depth" select="1" />

<xsl:param name="toc.section.depth" select="2" />
<xsl:param name="toc.max.depth" select="1" />
<xsl:param name="generate.section.toc.level" select="10" />
<xsl:param name="toc.list.type" select="'dl'" />

<xsl:param name="section.autolabel.max.depth" select="8" />
<xsl:param name="label.from.part" select="1" />
<xsl:param name="section.label.includes.component.label" select="1" />
<xsl:param name="component.label.includes.part.label" select="1" />

<xsl:param name="part.autolabel" select="'I'" />
<xsl:param name="chapter.autolabel" select="'1'" />
<xsl:param name="section.autolabel" select="'1'" />
<xsl:param name="appendix.autolabel" select="'A'" />
<xsl:param name="preface.autolabel" select="'i'" />
<xsl:param name="reference.autolabel" select="'I'" />
<xsl:param name="footnote.number.format" select="'1'" />
<xsl:param name="table.footnote.number.format" select="'a'" />

<xsl:param name="autotoc.label.in.hyperlink" select="0" />
<xsl:param name="autotoc.label.separator" select="'.&#xA0; '" />

<xsl:param name="html.extra.head.links" select="0" />
<xsl:param name="ulink.target" select="'_blank'" />
<xsl:param name="css.decoration" select="0" />
<xsl:param name="html.longdesc" select="0" />
<xsl:param name="generate.meta.abstract" select="1" />

<xsl:param name="generate.index" select="1" />

<xsl:param name="id.warnings" select="1" />

<xsl:param name="generate.manifest" select="1" />
<xsl:param name="manifest" select="'HTML.manifest'" />
<xsl:param name="manifest.in.base.dir" select="0" />

<xsl:param name="generate.legalnotice.link" select="1" />
<xsl:param name="html.head.legalnotice.link.types" select="'copyright license'" />
<xsl:param name="html.head.legalnotice.link.multiple" select="0" />

<xsl:param name="html.cellspacing" select="'1'" />

<xsl:param name="header.rule" select="1" />
<xsl:param name="footer.rule" select="1" />

<xsl:param name="inherit.keywords" select="0" />

<xsl:param name="make.valid.html" select="1" />
<xsl:param name="html.cleanup" select="1" />

<!--<![CDATA[ <xsl:param name="insert.xref.page.number" select="'yes'" /> ]]>-->
<!--<![CDATA[ <xsl:param name="insert.olink.page.number" select="'yes'" /> ]]>-->
<xsl:param name="insert.olink.pdf.frag" select="1" />
<xsl:param name="targets.filename" select="'link-target.db.xml'" />
<xsl:param name="target.database.document" select="'link-olinkdb.xml'" />
<xsl:param name="collect.xref.targets" select="'yes'" />
<xsl:param name="olink.doctitle" select="'yes'" />
<xsl:param name="prefer.internal.olink" select="0" />

<xsl:param name="generate.toc">
appendix  toc,title
article/appendix  nop
article   toc,title
book      toc,title,figure,table,example,equation
chapter   toc,title
part      toc,title
preface   toc,title
qandadiv  toc
qandaset  toc
reference toc,title
sect1     toc,title
sect2     toc,title
sect3     toc,title
sect4     toc,title
sect5     toc,title
section   toc,title
set       toc,title
</xsl:param>







<xsl:template match="*" mode="x.titleabbrev.markup.textonly">
	<xsl:variable name="titleabbrev">
		<xsl:apply-templates select="." mode="titleabbrev.markup"/>
	</xsl:variable>
	<xsl:value-of select="normalize-space($titleabbrev)"/>
</xsl:template>

<!--<![CDATA[
### Template to generate "breadcrumbs":
]]>-->
<xsl:template name="breadcrumbs">
	<xsl:param name="this.node" select="." />
	<div class="breadcrumbs">
		<xsl:for-each select="$this.node/ancestor::*">
			<span class="breadcrumb-parent">
				<a>
					<xsl:attribute name="href">
						<xsl:call-template name="href.target">
							<xsl:with-param name="object" select="." />
							<xsl:with-param name="context" select="$this.node" />
						</xsl:call-template>
					</xsl:attribute>
					<!--<![CDATA[ <xsl:apply-templates select="." mode="title.markup" /> ]]>-->
					<!--<![CDATA[ <xsl:apply-templates select="." mode="title.markup.textonly" /> ]]>-->
					<!--<![CDATA[ <xsl:apply-templates select="." mode="titleabbrev.markup" /> ]]>-->
					<xsl:apply-templates select="." mode="x.titleabbrev.markup.textonly" />
				</a>
			</span>
			<xsl:text> &#x2192; </xsl:text>
		</xsl:for-each>
		<!-- And display the current node, but not as a link -->
		<span class="breadcrumb-self">
			<!--<![CDATA[ <xsl:apply-templates select="$this.node" mode="title.markup" /> ]]>-->
			<xsl:apply-templates select="." mode="x.titleabbrev.markup.textonly" />
		</span>
	</div>
</xsl:template>

<!--<![CDATA[
### Call the "breadcrumbs" template for one of "user.header.navigation",
### "user.header.content" or override "header.navigation":
]]>-->
<xsl:template name="user.header.navigation">
	<xsl:call-template name="breadcrumbs" />
</xsl:template>


<!--<![CDATA[
### Additional stuff in the HTML <head>:
]]>-->
<xsl:template name="user.head.content">
	<!--<![CDATA[
	<xsl:if test="$html.extra.head.links == 0">
		<xsl:for-each select="//glossary[not(parent::article)]|glossary |//index[not(parent::article)]|index">
			<link rel="{local-name(.)}">
				<xsl:attribute name="href">
					<xsl:call-template name="href.target">
						<xsl:with-param name="context" select="$this"/>
						<xsl:with-param name="object" select="."/>
					</xsl:call-template>
				</xsl:attribute>
				<xsl:attribute name="title">
					<xsl:apply-templates select="." mode="object.title.markup.textonly"/>
				</xsl:attribute>
			</link>
		</xsl:for-each>
	</xsl:if>
	]]>-->
	
</xsl:template>



<!--<![CDATA[
<xsl:template name="user.footer.content">
	<hr />
	<a>
		<xsl:attribute name="href">
			<xsl:apply-templates select="//legalnotice[1]" mode="chunk-filename" />
		</xsl:attribute>
		
		<xsl:apply-templates select="//copyright[1]" mode="titlepage.mode" />
	</a>
</xsl:template>
]]>-->




<xsl:param name="use.extensions" select="1"></xsl:param>
<xsl:param name="tablecolumns.extension" select="0"></xsl:param><!-- because "No adjustColumnWidths function available." -->


<xsl:param name="x.docbook-xsl.version">
	<xsl:choose>
		<xsl:when exsl:foo="" test="1">1<xsl:message>
			<xsl:text>DocBook XSL stylesheets version: </xsl:text>
			<xsl:value-of select="$VERSION" />
		</xsl:message></xsl:when>
	</xsl:choose>
</xsl:param>

<xsl:param name="x.debug.exsl.node-set">
	<xsl:choose>
		<xsl:when exsl:foo="" test="function-available('exsl:node-set')">1<xsl:message>
			<xsl:text>function exsl:node-set() is available</xsl:text>
		</xsl:message></xsl:when>
		<xsl:otherwise>0<xsl:message>
			<xsl:text>function exsl:node-set() is NOT available</xsl:text>
		</xsl:message></xsl:otherwise>
	</xsl:choose>
</xsl:param>

<xsl:param name="x.debug.set.trailing">
	<xsl:choose>
		<xsl:when exsl:foo="" test="function-available('set:leading')">1<xsl:message>
			<xsl:text>function set:leading() is available</xsl:text>
		</xsl:message></xsl:when>
		<xsl:otherwise>0<xsl:message>
			<xsl:text>function set:leading() is NOT available</xsl:text>
		</xsl:message></xsl:otherwise>
	</xsl:choose>
</xsl:param>

<xsl:param name="x.debug.set.leading">
	<xsl:choose>
		<xsl:when exsl:foo="" test="function-available('set:trailing')">1<xsl:message>
			<xsl:text>function set:trailing() is available</xsl:text>
		</xsl:message></xsl:when>
		<xsl:otherwise>0<xsl:message>
			<xsl:text>function set:trailing() is NOT available</xsl:text>
		</xsl:message></xsl:otherwise>
	</xsl:choose>
</xsl:param>





<xsl:param name="html.extra.head.links.refentry" select="0" /><!-- non-standard param -->





<xsl:param name="x.user.outer.before">
</xsl:param>

<xsl:param name="x.user.outer.after">
	<div id="footer">
		<a target="_blank" href="http://www.amooma.de">Amooma GmbH</a> &#x2013; Bachstr. 126 &#x2013; 56566 Neuwied &#x2013; <span xml:lang="en">Germany</span><br />
		Tel. +49-2631-337000 &#x2013; E-Mail: <a target="_blank" href="mailto:info@amooma.de">info@amooma.de</a><br />
		<a target="_blank" href="http://www.amooma.de/gemeinschaft">www.amooma.de/gemeinschaft</a>
	</div>
	
	<script type="text/javascript" src="http://www.google-analytics.com/urchin.js"></script>
	<script type="text/javascript">
		<xsl:text disable-output-escaping="yes"><![CDATA[
			var _uacct = "UA-2879620-1";
			try {urchinTracker();} catch(e){}
		]]></xsl:text>
	</script>
</xsl:param>

<xsl:template name="x.user.outer.before">
	<xsl:if test="$x.user.outer.before">
		<xsl:comment> { x.user.outer.before </xsl:comment>
		<xsl:copy-of select="$x.user.outer.before" />
		<xsl:comment> x.user.outer.before } </xsl:comment>
	</xsl:if>
	
	<xsl:comment> { docbook content wrapper </xsl:comment>
</xsl:template>

<xsl:template name="x.user.outer.after">
	<xsl:comment> docbook content wrapper } </xsl:comment>
	
	<xsl:if test="$x.user.outer.after">
		<xsl:comment> { x.user.outer.after </xsl:comment>
		<xsl:copy-of select="$x.user.outer.after" />
		<xsl:comment> x.user.outer.after } </xsl:comment>
	</xsl:if>
</xsl:template>







<xsl:template name="chunk-element-content">
  <xsl:param name="prev"/>
  <xsl:param name="next"/>
  <xsl:param name="nav.context"/>
  <xsl:param name="content">
    <xsl:apply-imports/>
  </xsl:param>

  <xsl:call-template name="user.preroot"/>

  <html>
    <!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
    <xsl:call-template name="language.attribute"/>
    <!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->
    <xsl:call-template name="html.head">
      <xsl:with-param name="prev" select="$prev"/>
      <xsl:with-param name="next" select="$next"/>
    </xsl:call-template>

    <body>
      <xsl:call-template name="body.attributes"/>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
      <div id="page-margins">
      <xsl:call-template name="x.user.outer.before"/>
      <div id="dbk-wrapper">
      <xsl:call-template name="language.attribute"/>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->
      <xsl:call-template name="user.header.navigation"/>

      <xsl:call-template name="header.navigation">
        <xsl:with-param name="prev" select="$prev"/>
        <xsl:with-param name="next" select="$next"/>
        <xsl:with-param name="nav.context" select="$nav.context"/>
      </xsl:call-template>

      <xsl:call-template name="user.header.content"/>

      <xsl:copy-of select="$content"/>

      <xsl:call-template name="user.footer.content"/>

      <xsl:call-template name="footer.navigation">
        <xsl:with-param name="prev" select="$prev"/>
        <xsl:with-param name="next" select="$next"/>
        <xsl:with-param name="nav.context" select="$nav.context"/>
      </xsl:call-template>

      <xsl:call-template name="user.footer.navigation"/>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
      </div>
      <xsl:call-template name="x.user.outer.after"/>
      </div>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->
    </body>
  </html>
  <xsl:value-of select="$chunk.append"/>
</xsl:template>







<xsl:template match="footnote/para[1]|footnote/simpara[1]" priority="2">
	<!-- this only works if the first thing in a footnote is a para, -->
	<!-- which is ok, because it usually is. -->
	<xsl:variable name="name">
		<xsl:text>ftn.</xsl:text>
		<xsl:call-template name="object.id">
			<xsl:with-param name="object" select="ancestor::footnote"/>
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="href">
		<xsl:text>#</xsl:text>
		<xsl:call-template name="object.id">
			<xsl:with-param name="object" select="ancestor::footnote"/>
		</xsl:call-template>
	</xsl:variable>
	<!-- - - - - - - - - - - - - - - - - - - - - ORIGINAL { - - - - -->
	<!--<![CDATA[
	<p>
		<xsl:if test="@role and $para.propagates.style != 0">
			<xsl:apply-templates select="." mode="class.attribute">
				<xsl:with-param name="class" select="@role"/>
			</xsl:apply-templates>
		</xsl:if>
		<sup>
			<xsl:text>[</xsl:text>
			<a id="{$name}" href="{$href}">
				<xsl:apply-templates select="." mode="class.attribute"/>
				<xsl:apply-templates select="ancestor::footnote" mode="footnote.number"/>
			</a>
			<xsl:text>] </xsl:text>
		</sup>
		<xsl:apply-templates/>
	</p>
	]]>-->
	<!-- - - - - - - - - - - - - - - - - - - - - ORIGINAL } - - - - -->
	<!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
	<!-- call the "paragraph" template which in turn calls "unwrap.p": -->
	<xsl:call-template name="paragraph">
		<xsl:with-param name="class">
			<xsl:if test="@role and $para.propagates.style != 0">
				<xsl:value-of select="@role"/>
			</xsl:if>
		</xsl:with-param>
		<xsl:with-param name="content">
			<sup>
				<xsl:text>[</xsl:text>
				<a id="{$name}" href="{$href}">
					<xsl:apply-templates select="." mode="class.attribute"/>
					<xsl:apply-templates select="ancestor::footnote" mode="footnote.number"/>
				</a>
				<xsl:text>] </xsl:text>
			</sup>
			<xsl:apply-templates/>
		</xsl:with-param>
	</xsl:call-template>
	<!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->
</xsl:template>




<!--<![CDATA[
### Override the "html.head" template (from chunk-common.xsl).
### We don't need all those <link rel="refentry" ...> (man pages)
### links on every page.
]]>-->
<xsl:template name="html.head">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  <xsl:variable name="this" select="."/>
  <xsl:variable name="home" select="/*[1]"/>
  <xsl:variable name="up" select="parent::*"/>

  <head>
    <xsl:call-template name="system.head.content"/>
    <xsl:call-template name="head.content"/>

    <xsl:if test="$home">
      <link rel="start">
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="$home"/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:attribute name="title">
          <xsl:apply-templates select="$home" mode="object.title.markup.textonly"/>
        </xsl:attribute>
      </link>
    </xsl:if>

    <xsl:if test="$up">
      <link rel="up">
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="$up"/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:attribute name="title">
          <xsl:apply-templates select="$up" mode="object.title.markup.textonly"/>
        </xsl:attribute>
      </link>
    </xsl:if>

    <xsl:if test="$prev">
      <link rel="prev">
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="$prev"/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:attribute name="title">
          <xsl:apply-templates select="$prev" mode="object.title.markup.textonly"/>
        </xsl:attribute>
      </link>
    </xsl:if>

    <xsl:if test="$next">
      <link rel="next">
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="$next"/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:attribute name="title">
          <xsl:apply-templates select="$next" mode="object.title.markup.textonly"/>
        </xsl:attribute>
      </link>
    </xsl:if>

    <xsl:if test="$html.extra.head.links != 0">
      <!-- - - - - - - - - - - - - - - - - - - - - ORIGINAL { - - - - -->
      <!--<![CDATA[
      <xsl:for-each select="//part                             |//reference                             |//preface                             |//chapter                             |//article                             |//refentry                             |//appendix[not(parent::article)]|appendix                             |//glossary[not(parent::article)]|glossary                             |//index[not(parent::article)]|index">
      ]]>-->
      <!-- - - - - - - - - - - - - - - - - - - - - ORIGINAL } - - - - -->
      <!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
      <xsl:for-each select="//part                             |//reference                             |//preface                             |//chapter                             |//article                             |//appendix[not(parent::article)]|appendix                             |//glossary[not(parent::article)]|glossary                             |//index[not(parent::article)]|index">
      <!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->
        <link rel="{local-name(.)}">
          <xsl:attribute name="href">
            <xsl:call-template name="href.target">
              <xsl:with-param name="context" select="$this"/>
              <xsl:with-param name="object" select="."/>
            </xsl:call-template>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
          </xsl:attribute>
        </link>
      </xsl:for-each>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW { - - - - -->
      <xsl:if test="$html.extra.head.links.refentry != 0">
        <xsl:for-each select="//refentry">
          <link rel="{local-name(.)}">
            <xsl:attribute name="href">
              <xsl:call-template name="href.target">
                <xsl:with-param name="context" select="$this"/>
                <xsl:with-param name="object" select="."/>
              </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
            </xsl:attribute>
          </link>
        </xsl:for-each>
      </xsl:if>
      <!-- - - - - - - - - - - - - - - - - - - - - NEW } - - - - -->

      <xsl:for-each select="section|sect1|refsection|refsect1">
        <link>
          <xsl:attribute name="rel">
            <xsl:choose>
              <xsl:when test="local-name($this) = 'section'                               or local-name($this) = 'refsection'">
                <xsl:value-of select="'subsection'"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="'section'"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:attribute name="href">
            <xsl:call-template name="href.target">
              <xsl:with-param name="context" select="$this"/>
              <xsl:with-param name="object" select="."/>
            </xsl:call-template>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
          </xsl:attribute>
        </link>
      </xsl:for-each>

      <xsl:for-each select="sect2|sect3|sect4|sect5|refsect2|refsect3">
        <link rel="subsection">
          <xsl:attribute name="href">
            <xsl:call-template name="href.target">
              <xsl:with-param name="context" select="$this"/>
              <xsl:with-param name="object" select="."/>
            </xsl:call-template>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
          </xsl:attribute>
        </link>
      </xsl:for-each>
    </xsl:if>

    <!-- * if we have a legalnotice and user wants it output as a -->
    <!-- * separate page and $html.head.legalnotice.link.types is -->
    <!-- * non-empty, we generate a link or links for each value in -->
    <!-- * $html.head.legalnotice.link.types -->
    <xsl:if test="//legalnotice                   and not($generate.legalnotice.link = 0)                   and not($html.head.legalnotice.link.types = '')">
      <xsl:call-template name="make.legalnotice.head.links"/>
    </xsl:if>

    <xsl:call-template name="user.head.content"/>
  </head>
</xsl:template>


<!--<![CDATA[
### The DocBook XSl styhesheets don't have a template for <authorblurb>
### even though it's valid.
]]>-->
<xsl:template match="authorblurb">
	<div class="authorblurb">
		<xsl:apply-templates />
	</div>
</xsl:template>



</xsl:stylesheet>
