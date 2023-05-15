{$tn_width = min($derivative_params->max_width(), 150)}
<style type="text/css">{strip}
#contentTable {
  width:{math equation="x*4+200" x=$tn_width}px;
}

#the_image {
  text-align:center;
  margin:1em 0;
}
  #the_image a {
    border:none;
  }
  #the_image img {
    width:{$tn_width}px;
    height:{$tn_width}px;
  }

#button {
  margin-top:2em;
  text-align:center;
}

#button  a {
  padding:8px 15px;
  background:#A80D24;
  color:#fff;
  border:1px solid #CE2E5A;
  text-decoration:none;
  font-size:14px;
  font-weight:bold;
}
{/strip}</style>

<p>
  {'Hello <b>%s</b>, %s sent you a photos collection from <a href="%s">%s</a>'|translate:$PARAMS.recipient_name:$PARAMS.sender_name:$GALLERY_URL:$GALLERY_TITLE}
</p>

{if isset($THUMBNAILS)}
<p id="the_image">
{foreach from=$THUMBNAILS item=element}
  <a href="{$element.URL}" title="{$element.NAME|escape:html}">
    <img src="{$element.THUMB}" alt="{$element.TN_ALT|escape:html}" class="photo">
  </a>
{/foreach}
</p>
{/if}

{if $PARAMS.message}
<blockquote>{$PARAMS.message}</blockquote>
{/if}

<p id="button">
  <a href="{$COL_URL}">{'Click here to view the complete collection'|translate}</a>
</p>