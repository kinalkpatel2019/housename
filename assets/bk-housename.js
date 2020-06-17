(function($) {
  const calculate_price = function(len) {
    const selectedSize = $('.bk-letter-height .button.active').data('inch');
    const selectedMaterial = $('.bk-material .button.active').data(`price${selectedSize}`);
    const total = selectedMaterial*(len);
    $('.bk-total').html(parseFloat(total).toFixed(2));
    const install_method = $('.bk-delivery-option button.active').data('install');
    const inp = parseFloat(bk_housename.installation_rate).toFixed(2);
    const ship = parseFloat(bk_housename.delivery_rate).toFixed(2);
    let sinstall = inp;
    if('basic' === install_method) {
      sinstall = ship;
    }
    if('professional' === install_method) {
      sinstall = inp;
    }
    const inprice = isNaN(sinstall) ? 125: sinstall;
    const tax_percent = bk_housename.hst_gst || 10;
    const tax = (Number(total)+Number(inprice))*tax_percent*0.01;
    $('.bk-tax').html(parseFloat(tax).toFixed(2));
    let gtotal = (total+tax);
    gtotal = (Number(total)+Number(tax)+Number(inprice));
    $('.bk-grand-total').html(parseFloat(gtotal).toFixed(2));
  }

  const calculate_width = function(len) {
    const selheight = $('.bk-sel-height').html();
    const lwidth = bk_housename.lwidth;
    const selheightinch = `${selheight}inch`;
    const swidth = lwidth[selheightinch];
    const calWidth = parseFloat(len*swidth).toFixed(2);
    $('#bk-size-width').html(calWidth);
  }

  const bk_change_font = function(e) {
    $("#bk-address-field").removeClass('bk-error');
    const bkName = e.target.value;
    const bkNameNoSpaces = bkName.replace(/ /g,'');
    if(bkNameNoSpaces) {
      $('#bk-font-preview').html(bkName);
      const characters = bkNameNoSpaces.length;
      $('#bk-characters').html(characters);
      calculate_price(characters);
      calculate_width(characters);
    }
  }

  $('input#bk-address-field').keyup($.debounce(100, bk_change_font));

  $('#bk-fonts').change(function(e){
    const bkFontKey = $(this).find('option:selected').data("name");
    const selectedSize = $('.bk-letter-height .button.active').data('inch');
    const fontPreviewSize = `bk-fs-${selectedSize}`
    const bkNewFont = `bk-field bk-font-preview ${bkFontKey} ${fontPreviewSize}`;
    $("#bk-font-preview").removeClass().addClass(bkNewFont);
    $('.bk-sel-font').html($('#bk-fonts').val());
  });

  $('#bk-shadow').change(function(e){
    const shadowChecked = $(this).prop('checked');
    const fwrap = $('.bk-font-preview-wrap');
    if(shadowChecked) {
      fwrap.addClass('bk-font-shadow');
    } else {
      fwrap.removeClass('bk-font-shadow');
    }
  });

  $('#bk-installation').on("click", function(){
    const len = $('#bk-characters').html();
    calculate_price(len);
  });

  $('.bk-material').on('click','.button', function(){
    const selectedMaterial = $(this).html();
    const selMaterial = $(this).data('name');
    $('.bk-sel-material').html(selectedMaterial);
    $('.bk-material .button.active').removeClass('active');
    $(this).addClass('active');
    const len = $('#bk-characters').html();
    calculate_price(len);
    $('.bk-wall-font img').removeClass('active');
    $(`.${selMaterial}`).addClass('active');
  });

  //lustre
  $('.bk-material-lustre').on('click','.button', function(){
    const selectedlustre = $(this).html();
    const sellustre = $(this).data('name');
    $('.bk-sel-lustre').html(selectedlustre);
    $('.bk-material-lustre .button.active').removeClass('active');
    $(this).addClass('active');
    const len = $('#bk-characters').html();
    calculate_price(len);
    //$('.bk-wall-font img').removeClass('active');
    //$(`.${sellustre}`).addClass('active');
  });


  $('.bk-letter-height').on('click','.button', function(){
    const selectedSize = $(this).data('inch');
    $('.bk-size-height').html(selectedSize);
    $('.bk-letter-height .button.active').removeClass('active');
    $(this).addClass('active');
    const len = $('#bk-characters').html();
    calculate_price(len);
    calculate_width(len);
    const fontPreview = $('#bk-font-preview');
    fontPreview.attr('class', function(i, c){
        return c.replace(/(^|\s)bk-fs-\S+/g, '');
    });
    fontPreview.addClass(`bk-fs-${selectedSize}`);
  });

  $('.bk-wall-colors').on('click', 'button', function(){
    $('.bk-wall-colors button').removeClass('active');
    $(this).addClass('active');
    const selectedWall = $(this).data('wcolor');
    const inImg = `${selectedWall}.jpg`;
    const inClass = `bkw-${selectedWall}`;
    const fontPreviewWrap = $('.bk-font-preview-wrap');
    fontPreviewWrap.attr('class', function(i, c){
        return c.replace(/(^|\s)bkw-\S+/g, '');
    });
    if('reset.jpg' === inImg) {
      fontPreviewWrap.addClass(inClass).css({
        'background-image': 'none'
      });
      return;
    }
    fontPreviewWrap.addClass(inClass).css({
      'background-image': `url(/wp-content/plugins/bk-housename/assets/images/${inImg})`
    });
  });

  $('.bk-delivery-option').on('click', 'button', function(){
    const install_method = $(this).data('install');
    $('.bk-delivery-option button').removeClass('active');
    $(this).addClass('active');
    const len = $('#bk-characters').html();
    if('basic' === install_method) {
      $('#delivery-rate').show();
      $('#installation-rate').hide();
    } else if('professional' === install_method){
      $('#delivery-rate').hide();
      $('#installation-rate').show();
    }
    calculate_price(len);
  });

  const bk_add_to_cart = function(ftext) {
    if ( typeof wc_add_to_cart_params === 'undefined' ) {
      return false;
    }
    const fheight = jQuery('.bk-size-height').html();
    const fwidth = jQuery('#bk-size-width').html();
    const fsize = fheight.trim() + ' x ' + fwidth.trim() + ' inches';
    const tprice = jQuery('.bk-total').html();
    const fmaterial = jQuery('.bk-sel-material').html();
    const lustre = jQuery('.bk-sel-lustre').html();
    const sfont = jQuery('select[name=bk-fonts]').val();
    const sinstall= $('#bk-installation').prop("checked");
    const wall_surface = $('.bk-wall-colors button.active').html();
    const install_method = $('.bk-delivery-option button.active').data('install');
    const pid = 1192;
    let fprice = tprice;
    let inprice = 0;
    if('professional' === install_method) {
      const inp = parseFloat(bk_housename.installation_rate).toFixed(2);
      inprice = isNaN(inp) ? 125: inp;
      // console.log('PROFESSIONAL', inprice);
      fprice = parseFloat(tprice+inprice);
    } else if('basic' === install_method) {
      const ship = parseFloat(bk_housename.delivery_rate).toFixed(2);
      inprice = isNaN(ship) ? 50: ship;
      // console.log('BASIC', inprice);
      fprice = parseFloat(tprice+inprice);
    }
    const data = {
      action: 'bk_ajax_add_to_cart',
      product_id: pid,
      quantity: 1,
      bk_text: ftext.trim(),
      bk_tprice: tprice,
      bk_material: fmaterial.trim(),
      bk_size: fsize,
      bk_font: sfont.trim(),
      bk_installation: install_method,
      bk_installation_price: inprice,
      bk_wall_surface: wall_surface,
      bk_lustre:lustre.trim()
    };
    $.post(wc_add_to_cart_params.ajax_url,data,function(response){
      console.log('Ajax Res', response);
      if (!response) return;
      const resjson = JSON.parse(response);
      if(resjson && resjson.success) {
        console.log('Success', resjson.redirect_url);
        window.location.href = resjson.redirect_url;
      }
      if ( resjson.error && resjson.redirect_url ) {
        window.location.href = resjson.redirect_url;
        return;
      }
    });
  }

  $("#bk-order").click(function(){
    const text = $("#bk-font-preview").html();
    const ftext = text.trim();
    const fotext = $("#bk-address-field").val();
    const selFont = $('.bk-select').val();
    if(fotext.length < 1) {
      $("#bk-address-field").addClass('bk-error');
      alert('Please enter address');
      return;
    }
    if('Select Font' === selFont){
      $('.bk-select').addClass('bk-error');
      alert('Please select font');
      return;
    } else {
      $(this).html('Processing...').attr('disabled', 'disabled');
      bk_add_to_cart(ftext);
    }

  });

})(jQuery);
