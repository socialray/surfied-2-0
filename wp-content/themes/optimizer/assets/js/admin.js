// JavaScript Document
jQuery(window).ready(function() {

//Blocks Accordion
jQuery("#section-section-block1-start h3, #section-section-block2-start h3, #section-section-block3-start h3, #section-section-block4-start h3, #section-section-block5-start h3, #section-section-block6-start h3").prepend('<i class="el-icon-plus" /> ');
jQuery("#section-table-section-block1-start, #section-table-section-block2-start, #section-table-section-block3-start, #section-table-section-block4-start, #section-table-section-block5-start, #section-table-section-block6-start").css({"height":"0px", "overflow":"hidden", "display":"block"});

jQuery('#section-section-block1-start, #section-section-block2-start, #section-section-block3-start, #section-section-block4-start, #section-section-block5-start, #section-section-block6-start').toggle(function(){ 
jQuery("#section-table-section-block1-start, #section-table-section-block2-start, #section-table-section-block3-start, #section-table-section-block4-start, #section-table-section-block5-start, #section-table-section-block6-start").animate({"height":"0px"});
			jQuery(this).next().animate({"height":"800px"});
			jQuery(this).find("h3 i").removeClass("el-icon-plus").addClass("el-icon-minus");
		},function(){
			jQuery(this).next().animate({"height":"0px"});
			jQuery(this).find("h3 i").removeClass("el-icon-minus").addClass("el-icon-plus");
		});

//SLIDE CTA BUTTONS
jQuery("#optimizer-static_cta1_link, #optimizer-static_cta1_txt_style").appendTo(".tr_optimizer-static_cta1_text td");
jQuery("#optimizer-static_cta1_bg_color, #optimizer-static_cta1_txt_color").appendTo(".tr_optimizer-static_cta1_text td");

jQuery("#optimizer-static_cta2_link, #optimizer-static_cta2_txt_style").appendTo(".tr_optimizer-static_cta2_text td");
jQuery("#optimizer-static_cta2_bg_color, #optimizer-static_cta2_txt_color").appendTo(".tr_optimizer-static_cta2_text td");

jQuery(".tr_optimizer-static_cta1_link, .tr_optimizer-static_cta1_bg_color, .tr_optimizer-static_cta1_txt_color, .tr_optimizer-static_cta1_txt_style, .tr_optimizer-static_cta2_link, .tr_optimizer-static_cta2_bg_color, .tr_optimizer-static_cta2_txt_color, .tr_optimizer-static_cta2_txt_style").hide();



///Documentation
jQuery(".docu_front").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_front").delay(300).fadeIn();});
jQuery(".docu_img").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_img").delay(300).fadeIn();});
jQuery(".docu_vid").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_vid").delay(300).fadeIn();});
jQuery(".docu_blog").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_blog").delay(300).fadeIn();});
jQuery(".docu_contct").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_contct").delay(300).fadeIn();});
jQuery(".docu_bg").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_bg").delay(300).fadeIn();});
jQuery(".docu_headr").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_headr").delay(300).fadeIn();});
jQuery(".docu_menu").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_menu").delay(300).fadeIn();});
jQuery(".docu_styling").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_styling").delay(300).fadeIn();});
jQuery(".docu_wdgts").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_wdgts").delay(300).fadeIn();});
jQuery(".docu_shorts").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_shorts").delay(300).fadeIn();});
jQuery(".docu_supp").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_supp").delay(300).fadeIn();});
jQuery(".docu_gallery").click(function() {jQuery('#ast_docu').fadeOut();jQuery("#docu_gallery").delay(300).fadeIn();});

jQuery(".docuback").click(function() {jQuery('#docu_gallery, #docu_front, #docu_img, #docu_vid, #docu_blog, #docu_contct, #docu_bg, #docu_headr, #docu_menu, #docu_styling, #docu_wdgts, #docu_shorts, #docu_supp').fadeOut();jQuery("#ast_docu").delay(300).fadeIn();});

//UPGRADE
	jQuery("#sub_ex, #sub_compare").hide();
	jQuery('.up_ul li').click(function(){
    	jQuery('.up_ul li').removeClass("active");
    	jQuery(this).addClass("active");
	});
	
		jQuery('.ast_why_pro').click(function(){ 
			jQuery(".sub_ex, .sub_compare").hide();
			jQuery(".sub_feat").fadeIn(300); 
		});
		jQuery('.ast_example').click(function(){ 
			jQuery(".sub_feat, .sub_compare").hide();
			jQuery(".sub_ex").fadeIn(300); 
		});
		jQuery('.ast_compare').click(function(){ 
			jQuery(".sub_ex, .sub_feat").hide();
			jQuery(".sub_compare").fadeIn(300); 
		});



jQuery("#info-optim_upgrade").parent().find("h3:first").hide();
	
	jQuery(".prem_box").toggle(function(){
		jQuery(".prem_box p").removeClass("active");
		jQuery(this).animate({"opacity":"1"}).siblings().animate({"opacity":"0.3"});
		jQuery(this).find("p").addClass("active");
	}, function(){
		jQuery(this).find("p").removeClass("active");
		jQuery(this).siblings().animate({"opacity":"1"});
	});

jQuery('.pro strong').qtip({  content: { attr: 'data-tooltip'},    style: {classes: 'qtip-tipsy' } });

(function($){$.fn.alterClass=function(removals,additions){var self=this;if(removals.indexOf("*")===-1){self.removeClass(removals);return!additions?self:self.addClass(additions)}var patt=new RegExp("\\s"+removals.replace(/\*/g,"[A-Za-z0-9-_]+").split(" ").join("\\s|\\s")+"\\s","g");self.each(function(i,it){var cn=" "+it.className+" ";while(patt.test(cn))cn=cn.replace(patt," ");it.className=$.trim(cn)});return!additions?self:self.addClass(additions)}})(jQuery);

/*CONVERSION MESSAGE*/
jQuery('#redux-header .display_header').after('<div class="convert_warning"><p>'+objectL10n.line1+'</p><p>'+objectL10n.line2+'</p></div>');


});

/**
 * jQuery Unveil
 * A very lightweight jQuery plugin to lazy load images
 * http://luis-almeida.github.com/unveil
 *
 * Licensed under the MIT license.
 * Copyright 2013 Luís Almeida
 * https://github.com/luis-almeida
 */

(function(e){e.fn.unveil=function(t,n){function f(){var t=u.filter(function(){var t=e(this);if(t.is(":hidden"))return;var n=r.scrollTop(),s=n+r.height(),o=t.offset().top,u=o+t.height();return u>=n-i&&o<=s+i});a=t.trigger("unveil");u=u.not(a)}var r=e(window),i=t||0,s=window.devicePixelRatio>1,o=s?"data-src-retina":"data-src",u=this,a;this.one("unveil",function(){var e=this.getAttribute(o);e=e||this.getAttribute("data-src");if(e){this.setAttribute("src",e);if(typeof n==="function")n.call(this)}});r.on("scroll.unveil resize.unveil lookup.unveil",f);f();return this}})(window.jQuery||window.Zepto);




jQuery(document).ready(function() {
  jQuery(".screenp img").unveil();
});