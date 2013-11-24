{'Hello %s, %s sent you a photos collection from "%s"'|translate:$PARAMS.recipient_name:$PARAMS.sender_name:$GALLERY_TITLE}

{if $PARAMS.message}
----
{$PARAMS.message}
----
{/if}

{'Click here to view the complete collection'|translate} : {$COL_URL}