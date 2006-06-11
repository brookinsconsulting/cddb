<form method="post" action={concat('/cddb/discinfo/',$category,'/',$disc.discid)|ezurl}>
<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title"><img src={'cddb/32x32/cdaudio_unmount.png'|ezimage} /> {$disc.artist|wash} / {$disc.title|wash}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-information">
<p>{'ID'|i18n( 'extension/cddb' )}: {$category|wash} / {$disc.discid|wash}, {'revision'|i18n( 'extension/cddb' )}: {$disc.revision|wash}</p>
</div>

<div class="context-attributes">
    <div class="block"><label>{'Title'|i18n( 'extension/cddb' )}:</label> {$disc.title|wash}</div>
    <div class="block"><label>{'Artist'|i18n( 'extension/cddb' )}:</label> {$disc.artist|wash}</div>
    <div class="block"><label>{'Genre'|i18n( 'extension/cddb' )}:</label> {$disc.genre|wash}</div>
    <div class="block"><label>{'Year'|i18n( 'extension/cddb' )}:</label> {$disc.year|wash}</div>
    <div class="block"><label>{'Tracks'|i18n( 'extension/cddb' )}:</label> {$disc.num_tracks|wash}</div>
    <div class="block"><label>{'Total time'|i18n( 'extension/cddb' )}:</label> {$disc.length_formatted|wash}</div>
</div>

{* DESIGN: Content END *}</div></div></div>

{* Buttonbar for content window. *}
<div class="controlbar">

{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">

<div class="block">
<div class="left">
<input type="submit" class="button" value="Import" name="ImportButton" />
</div>

<div class="break"></div>

</div>

{* DESIGN: Control bar END *}</div></div></div></div></div></div>

</div>

</div>

</form>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">{'Track list'|i18n( 'extension/cddb' )}</h2>

{* DESIGN: Mainline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<table class="list" cellspacing="0">
<tr>
    <th class="tight">N°</th>
    <th>Artist</th>
    <th>Title</th>
    <th>Length</th>
</tr>
{foreach $disc.tracks as $index => $track sequence array( 'bglight', 'bgdark' ) as $sequence}
    <tr class="{$sequence}">
        <td style="text-align:right;">{$index|inc}</td>
        <td>{$track.artist|wash}</td>
        <td>{$track.title|wash}</td>
        <td>{$track.length_formatted|wash}</td>
    </tr>
{/foreach}
</table>

{* DESIGN: Content END *}</div></div></div></div></div></div>

</div>
