<form method="post" action={"cddb/search"|ezurl}>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'CDDB lookup'|i18n( 'extension/cddb' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

<div class="block">
<label>Length:</label> <input type="text" name="length" {if is_set($length)}value="{$length|wash}" {/if}/>
</div>
<div class="block">
<label>Track offsets:</label> <input type="text" size="80" name="offsets" {if is_set($offsets)}value="{$offsets|wash}" {/if}/>
</div>

</div>

{* DESIGN: Content END *}</div></div></div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
<input type="submit" name="SearchButton" value="{'Search'|i18n( 'extension/cddb' )}" class="button" />
</div>

{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">{'Raw query'|i18n( 'extension/cddb' )}</h2>

{* DESIGN: Mainline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

<div class="block"><a href="http://webplaza.pt.lu/~vigatol/discidcalc/">DiscID Calculator</a> will give you a useable query, in the form <i>cddb query [discid] [num_tracks] [offset_1] [offset_2] ... [offset_n] [length]</i>.</div>
<div class="block">
<label>Query:</label> 
<input type="text" name="query" size="100" {if is_set($query)}value="{$query|wash}" {/if}/>
</div>

</div>

{* DESIGN: Content END *}</div></div></div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
<input type="submit" name="RawSearchButton" value="{'Search with raw query'|i18n( 'extension/cddb' )}" class="button" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>

{if is_set( $discs )}
<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">{'Search results'|i18n( 'extension/cddb' )}</h2>

{* DESIGN: Mainline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

{if $discs|count|eq(0) }
<p>No discs found.</p>
{else}
<table class="list" cellspacing="0">
<tr>
    <th class="tight">&nbsp;</th>
    <th>Artist</th>
    <th>Title</th>
    <th>CDDB category</th>
</tr>
{foreach $discs as $disc sequence array( 'bglight', 'bgdark' ) as $sequence}
    <tr class="{$sequence}">
        <td><a href={concat('cddb/discinfo/', $disc.category, '/', $disc.discid)|ezurl}><img src={'cddb/16x16/cdaudio_unmount.png'|ezimage} /></a></td>
        <td>{$disc.artist|wash}</td>
        <td>{$disc.title|wash}</td>
        <td>{$disc.category|wash}</td>
    </tr>
{/foreach}
</table
{/if}

{/if}

{* DESIGN: Content END *}</div></div></div></div></div>

</div>

</form>