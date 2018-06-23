function NaiveFlexBoxSupport(d){
  var f = "flex", e = d.createElement('b');
  e.style.display = f;
  return e.style.display == f;
}
// Simulates flex display in old browsers
if(!NaiveFlexBoxSupport(document)){
  function jResize() {
    var $jmos = $('.jmodal');
    if ($jmos.length > 0) {
      var hW = $(window).height(),
      hO, mT;

      $.each($jmos, function(i,obj) {
        obj = $(obj);
        hO = obj.outerHeight();
        mT = parseInt((hW-hO)/2);

        if (mT < 0) {
          obj.css('margin-top', 0).closest('.jmodal-wrapper').css('overflow-Y', 'auto');
          $('body,html').addClass('jmodal-body-scroll');
        } else {
          obj.css('margin-top', mT).closest('.jmodal-wrapper').css('overflow-Y', 'hidden');
          $('body,html').removeClass('jmodal-body-scroll');
        }
      });
    } else {
      $('body,html').removeClass('jmodal-body-scroll');
    }
  }
  // Remove this if if underscore is already included
  if(typeof _ != 'undefined'){
    $(window).bind('resize.jmodal', _.throttle(jResize, 350));
  }
  else{
    $(window).bind('resize.jmodal', jResize)
  };
}

var jModal = function(options) {
  var config = {
    bgClose: true,//bool - background close
    close: true,//bool - is closeble
    content: '',//string - message
    responsive: true,//bool
    width: 'auto',//size,
    height: 'auto',//size,
		maxHeight: 'auto',
		maxWidth: '100%',
    onShow: function(){},//function - on appear
    onClose: function(){},//function
    onConfirm: false,//function/false
    onCancel: false,//function/false
    className: ''
  },
  html = {
    bg: '<div class="jmodal-wrapper"><div class="jmodal"><div class="jmodal-content"></div></div></div>',
    closeBtn: '<button class="btn-close fa fa-close jClose"></button>',
    confirmBtn: '<input type="button" name="buttonConfirm" class="jConfirm btn btn-primary" value="Confirmar">',
    cancelBtn: '<input type="button" name="buttonConfirm" class="jCancel btn btn-default" value="Cancelar">',
    btns: '<div class="btns"></div>'
  },
  $jm = $(html.bg);//parcial
  $jm.responsive = config.responsive;

  $.extend(config, typeof options == 'object' ? options : {content: options});
  $jm.addClass(config.className);
  $jm.find('.jmodal-content').append($(this)[0] instanceof Element ? $(this).clone(false).removeClass('hidden') : config.content);
  $('body').append($jm);

	if (config.responsive) {
		$jm.find('.jmodal').addClass('responsive');
	}

  $jm.find('.jmodal').css({
		width: config.width,
		height: config.height,
		maxHeight: config.maxHeight,
		maxWidth: config.maxWidth
	});

  if (config.height != 'auto') {
    $jm.find('.jmodal-content').css('height', '100%');
  }

  $jm.close = function(e) {
    if (typeof e == 'undefined' || e.target == this) {
      var close = config.onClose();
      if (typeof close == 'undefined' || close) {
        $jm.remove();
        $(window).trigger('resize');
        delete $jm;
      }
    }
  };

  $jm.onCancel = config.onCancel;

  $jm.cancel = function(e) {
    var cancel  = typeof $jm.onCancel == 'function' ? $jm.onCancel() : $jm.onCancel;

    if (cancel || typeof cancel == 'undefined') {
      $jm.close();
    }
  };

  $jm.onConfirm = config.onConfirm;

  $jm.confirm = function() {
    var confirm  = typeof $jm.onConfirm == 'function' ? $jm.onConfirm() : $jm.onConfirm;

    if (confirm || typeof confirm == 'undefined') {
      $jm.close();
    }
  };

  if (config.close) {
    if (!$jm.find('.jClose').length) {
      $jm.find('.jmodal').prepend(html.closeBtn);
    }

    if (config.bgClose) {
      $jm.bind('click', $jm.close);
    }
  }

  if (config.onCancel && !$jm.find('.jCancel').length) {
    if (!$jm.find('.btns').length) {
      $jm.find('.jmodal').append(html.btns);
    }
    $jm.find('.btns').append(html.cancelBtn);
  }

  if (config.onConfirm && !$jm.find('.jConfirm').length) {
    if (!$jm.find('.btns').length) {
      $jm.find('.jmodal').append(html.btns);
    }
    $jm.find('.btns').append(html.confirmBtn);
  }

  function bind(e, func) {
    e = $jm.find(e);
    if (e.length) {
      e.bind('click', func);
    }
  }

  $jm.on('click', '.jClose', $jm.close);
  $jm.on('click', '.jCancel', $jm.cancel);
  $jm.on('click', '.jConfirm', $jm.confirm);

  if(jResize) {
    $jm.on('DOMSubtreeModified.jmodal', _.throttle(jResize, 400));
    jResize();
  }

  $jm.onShow = config.onShow;
  $jm.onShow();

  return $jm;
};

$.fn.jModal = jModal;

// ESC key close
$(document).keyup(function(e) {
  if (e.keyCode == 27) { // escape key maps to keycode `27`
    // <DO YOUR WORK HERE>
  }
});
