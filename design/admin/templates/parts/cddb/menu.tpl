{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h4>{'CDDB'|i18n( 'extension/cddb' )}</h4>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

{section show=eq( $ui_context, 'edit' )}
<ul>
    <li><div><span class="disabled">{'Search'|i18n( 'extension/cddb' )}</span></div></li>
    <li><div><span class="disabled">{'Submit'|i18n( 'extension/cddb' )}</span></div></li>
    <li><div><span class="disabled">{'Settings'|i18n( 'extension/cddb' )}</span></div></li>
</ul>

{section-else}

<ul>
    <li><div><a href={'/cddb/search/'|ezurl}>{'Search'|i18n( 'extension/cddb' )}</a></div></li>
    <li><div><a href={'/cddb/submit/'|ezurl}>{'Submit'|i18n( 'extension/cddb' )}</a></div></li>
    <li><div><a href={'/cddb/settings/'|ezurl}>{'Settings'|i18n( 'extension/cddb' )}</a></div></li>
</ul>

{/section}

{* DESIGN: Content END *}</div></div></div></div></div></div>