{footer_script require='jquery'}
var bg_color = jQuery('#the_page #content').css('background-color');
if (!bg_color || bg_color=='transparent') {
  bg_color = jQuery('body').css('background-color');
}

console.log('{$U_SHARE}');

{if isset($U_SHARE)}
  var $share_form = jQuery('#share_form');

  // functions
  jQuery.fn.extend({
      hideVis: function() {
          jQuery(this).css('visibility', 'hidden');
          return this;
      },
      showVis: function() {
          jQuery(this).css('visibility', 'visible');
          return this;
      },
      toggleVis: function(toggle) {
          if (jQuery(this).css('visibility')=='hidden' || toggle === true){
              return jQuery(this).showVis();
          } else {
              return jQuery(this).hideVis();
          }
      }
  });

  function enterShareKeyEdit() {
      $share_form.find('.url-edit').show();
      $share_form.find('.url-normal').hide();
      jQuery('.share_colorbox_open').colorbox.resize({ldelim}speed:0});
  }
  function exitShareKeyEdit() {
      $share_form.find('.url-edit').hide();
      $share_form.find('.url-normal').show();
      jQuery('.share_colorbox_open').colorbox.resize({ldelim}speed:0});
  }

  // hide some inputs
  exitShareKeyEdit();

  // display key
  $share_form.find('.url-more').text($share_form.find('input[name="share_key"]').val());

  // url edition
  $share_form.find('.edit_share_key').on('click', function(e) {
      enterShareKeyEdit();
      e.preventDefault();
  });
  $share_form.find('.set_share_key').on('click', function(e) {
      if ($share_form.find('input[name="share_key"]').val().length < 8) {
          alert('{'The key must be at least 8 characters long'|translate|escape:javascript}');
      }
      else {
          $share_form.find('.url-more').text($share_form.find('input[name="share_key"]').val());
          exitShareKeyEdit();
      }
      e.preventDefault();
  });
  $share_form.find('.cancel_share_key').on('click', function(e) {
      $share_form.find('input[name="share_key"]').val($share_form.find('.url-more').text());
      exitShareKeyEdit();
      e.preventDefault();
  });
  $share_form.find('.url-more').on('dblclick', function() {
      enterShareKeyEdit();
  });

  // optional inputs
  $share_form.find('.share-option').each(function() {
      $share_form.find('input[name="'+ jQuery(this).data('for') +'"]').hideVis();
  }).on('change', function() {
      $share_form.find('input[name="'+ jQuery(this).data('for') +'"]').toggleVis($(this).is(':checked'));
  });

  // datetime picker
  $share_form.find('input[name="share_deadline"]').datetimepicker({
      dateFormat: 'yy-mm-dd',
      minDate: new Date()
  });


  // popup
  jQuery('.share_colorbox_open').colorbox({
    {if isset($share.open)}open: true, transition:"none",{/if}
    inline:true
  });
  jQuery('.share_colorbox_close').click(function(e) {
    jQuery('.share_colorbox_open').colorbox.close();
    e.preventDefault();
  });
  jQuery('#share_form').css('background-color', bg_color);
{/if}

{if isset($U_MAIL)}
  jQuery('.mail_colorbox_open').colorbox({
    {if isset($contact.open)}open: true, transition:"none",{/if}
    inline:true
  });
  jQuery('.mail_colorbox_close').click(function(e) {
    jQuery('.mail_colorbox_open').colorbox.close();
    e.preventDefault();
  });

  jQuery('#mail_form [name=to]').on('change', function() {
    $('.recipient-input').toggle(jQuery(this).val() == 'email');
    jQuery.colorbox.resize();
  });

  jQuery('#mail_form').css('background-color', bg_color);
{/if}

jQuery('#edit_form_show').click(function() {
  jQuery('.collection-edit').hide();
  jQuery('.additional_info').hide();
  jQuery('#edit_form').show();
});
jQuery('#edit_form_hide').click(function() {
  jQuery('.collection-edit').show();
  jQuery('.additional_info').show();
  jQuery('#edit_form').hide();
});
{/footer_script}