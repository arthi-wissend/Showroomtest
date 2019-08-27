(function ($) {
	$.fn.unveil = function (threshold, callback) {

		var $w = $(window),
			th = threshold || 0,
			retina = window.devicePixelRatio > 1,
			attrib = retina ? "data-src-retina" : "data-src",
			images = this,
			loaded;

		this.one("unveil", function () {
			var source = this.getAttribute(attrib);
			source = source || this.getAttribute("data-src");
			if (source) {
				if (this.tagName === 'IMG') {
					this.setAttribute("src", source);
					if (typeof callback === "function") {
						callback.call(this);
					}
				} else {
					var bgImg = new Image();
					var el = this;
					bgImg.onload = function () {
						el.style.backgroundImage = 'url(' + source + ')';
						if (typeof callback === "function") {
							callback.call(el);
						}
					};
					bgImg.src = source;
				}
			}
			this.removeAttribute(attrib);
		});

		function unveil() {
			var inview = images.filter(function () {
				var $e = $(this);
				if ($e.is(":hidden")) return;

				var wt = $w.scrollTop(),
					wb = wt + $w.height(),
					et = $e.offset().top,
					eb = et + $e.height();

				return eb >= wt - th && et <= wb + th;
			});

			loaded = inview.trigger("unveil");
			images = images.not(loaded);
		}

		$w.on("scroll.unveil resize.unveil lookup.unveil", unveil);

		unveil();

		return this;
	};
})(window.jQuery || window.Zepto);

function loadModelJSON(fileRef, callback) {
	jQuery.ajax({
		type: "GET",
		dataType: "json",
		url: applicationPath + '/cache/products/' + fileRef + '.json',
		async: true,
		cache: false,
		success: function (json) {
			var data = json;
			callback(data);
		},
		error: function (xhr, textStatus, errorThrown) {
			alert(JSON.stringify(xhr, null, 4));
			console.log(errorThrown);
		}
	});
}

jQuery(document).ready(function () {
	jQuery('.lazy-bg').unveil(0, function () {
		this.style.opacity = 1;
	});
	var layout = 'grid';

	var containerEl1 = document.querySelector('[data-ref="product-container"]');
	var containerEl2 = document.querySelector('[data-ref="comparator-container"]');

	var config = {
		layout: {
			containerClassName: 'grid',
		},
		load: {
			sort: 'price:asc'
		},
		animation: {
			applyPerspective: false
		},
		callbacks: {
			onMixEnd: function () {
				jQuery('img').unveil(0, function () {
					jQuery(this).load(function () {
						this.style.opacity = 1;
					});
				});
				if (!jQuery('.overview').hasClass('loaded')) {
					setTimeout(function () {
						jQuery('.overview').addClass('loaded')
					}, 500);
				}
			}
		}
	}

	var mixer1 = mixitup(containerEl1, config);
	var mixer2 = mixitup(containerEl2, config);

	mixer1.filter(jQuery('.nav').find('.filter').eq(0).attr('data-filter'));
	mixer2.filter(jQuery('.nav').find('.filter').eq(0).attr('data-filter'));

	jQuery('h1').html(jQuery('.nav').find('.filter').eq(0).find('.title').html());

	jQuery('.nav').find('.filter').click(function () {
		jQuery('h1').html(jQuery(this).find('.title').html());
	});

	jQuery('#ChangeLayout').on('click', function () {
		if (layout === 'list') {
			layout = 'grid';

			jQuery(this).text(lang.comparison);

			mixer1.changeLayout(layout);
			mixer2.changeLayout(layout);

			jQuery('.comparator-wrapper').removeClass('show');
		} else {
			layout = 'list';

			jQuery(this).text(lang.overview);

			mixer1.changeLayout(layout);
			mixer2.changeLayout(layout);

			jQuery('.comparator-wrapper').addClass('show');
		}
	});

	jQuery('.preview').on('click', function (event) {
		animateProductPage(jQuery(this), 'open');
	});
	jQuery('.back-button').on('click', function (event) {
		animateProductPage('', 'close');
	});

	jQuery('.main-container').on('click', '*', function (e) {
		if(e.which) {
			if(jQuery('.main-container').hasClass('welcome')) {
				jQuery('.main-container').removeClass('welcome');
			}
		}
	});

	(function () {
		var t;
		document.onmousemove = resetTimer;
		document.onkeypress = resetTimer;
		document.ontouchstart = resetTimer;
		window.onload = resetTimer;

		function reset() {
			jQuery('.main-container').removeClass('product-detail-open');
			jQuery('.main-container').addClass('welcome');
			jQuery('body').scrollTop(0);
			jQuery('.overlay-qr').removeClass('show');
			jQuery('.categories').find('.filter').eq(0).trigger('click');
			jQuery('.categories').scrollLeft(0);
		}

		function resetTimer() {
			clearTimeout(t);
			t = setTimeout(reset, 1000 * 60 * screenSaverTimeout);
		}
	})();
});

jQuery(document).keyup(function (event) {
	if (event.which == '27') {
		animateProductPage('', 'close');
	}
});

var settingsCt = 0;
jQuery(document).on('click', function (e) {
	if (e.target.className === 'settings') {
		settingsCt++;
		if (settingsCt > 5) {
			if (jQuery(this).attr('data-tc') && jQuery(this).attr('data-tid')) {
				ga('send', 'pageview');
			}
			window.localStorage.clear();
			deleteCookie('ds_cat_sort');
			window.location.href = applicationPath;
		}
	} else {
		settingsCt = 0;
	}
});

jQuery(document).on('click', '*', function (e) {
	if(e.which) {
		if (e.target.className !== 'settings') {
			if (jQuery(this).attr('data-tc') && jQuery(this).attr('data-tid')) {
				ga('send', 'event', jQuery(this).attr('data-tc'), 'click-' + jQuery(this).attr('data-tid'), jQuery('.main-container').attr('data-store-name') + ' - ' + jQuery('.main-container').attr('data-store-id'), 0);
			}
			if (jQuery(this).attr('data-target') && jQuery(this).hasClass('qr-link')) {
				openOverlayQR(jQuery(this).attr('data-target'), lang.scan_qr_link);
			}
		}
	}
});


function makeCode(value, target) {
	var qrcode = new QRCode(target, {
		useSVG: true,
		colorDark: "#0082c3"
	});
	qrcode.makeCode(value);
}

function link2QR(str) {
	var urlPattern = /([^+>]*)[^<]*(<a [^>]*(href="([^>^\"]*)")[^>]*>)([^<]+)(<\/a>)/gi;
	try {
		return str.replace(urlPattern, '$1<a class="qr-link" data-target="$4">$5</a>');
	}
	catch {
		return str;
	}
}

var productInfo;

function animateProductPage(product, animationType) {
	if (animationType == 'open') {
		product.addClass('active');
		var ref = product.parents('.product').attr('data-dsm-code') + '_' + product.parents('.product').attr('data-product-id');
		
		loadModelJSON(ref, function (data) {
			productInfo = data;
			var sliderImage = '';
			jQuery.each(productInfo.media.images, function (i, v) {
				sliderImage += '<div class="image"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy="' + productInfo.media.images[i] + '?f=754x754" alt="" /></div>';
			});
			if (jQuery('.product-detail').find('.slider.main, .slider.nav').hasClass('slick-initialized')) {
				jQuery('.product-detail').find('.slider.main, .slider.nav').slick('unslick');
				jQuery('.product-detail').find('.slider.main, .slider.nav').html('');
			}
			jQuery('.product-detail').find('.slider.main, .slider.nav').html(sliderImage);

			jQuery('.product-detail').find('.product-content .brand').html(productInfo.productInfo.brandId);
			jQuery('.product-detail').find('.product-content .name').html(productInfo.productInfo.description);
			jQuery('.product-detail').find('.product-content .ref').html(ref.replace('_',' / '));

			var price = productInfo.skus[0].activePrice.split('.');
			if (price.length > 1) {
				price = price[0] + ',' + (price[1].length == 1 ? price[1] + '0' : price[1]);
			} else {
				price = price[0];
			}
			jQuery('.product-detail').find('.product-content .price .content').html(price + '€');

			var ct = 0,
				stars = '';
			stars += '<div class="stars">';
			while (ct < 5) {
				if (ct < productInfo.productInfo.averageRating) {
					if (ct < productInfo.productInfo.averageRating && productInfo.productInfo.averageRating < ct + 1) {
						stars += '<span class="icon icon-star half"></span>';
					} else {
						stars += '<span class="icon icon-star"></span>';
					}
				} else {
					stars += '<span class="icon icon-star disabled"></span>';
				}
				ct++;
			}
			stars += '</div>';

			var reviews = '';
			if (productInfo.productInfo.ratingNumber > 0) {
				reviews = productInfo.productInfo.ratingNumber + ((productInfo.productInfo.ratingNumber > 1) ? ' ' + lang.reviews : ' ' + lang.review);
			} else {
				reviews = lang.no_reviews;
			}
			jQuery('.product-detail').find('.product-content .reviews').html(stars + ' <div class="label">' + reviews + '</div>');

//			jQuery('.product-detail').find('.product-content .stars, .product-content .label').click(function () {
//				var url = 'https://content.decathlon.de/qr/product/?pid=' + productInfo.productInfo.modelCode + '&eo=' + jQuery('.main-container').attr('data-store-id');
//				openOverlayQR(url, lang.scan_qr_infos);
//			});

//			var url = 'https://www.decathlon.de//p/_/R-p-187192' + productInfo.productInfo.productId + '.html';
//			makeCode(url, document.getElementById('qrcode'));

			jQuery('.overlay-qr').click(function (e) {
				if (e.target !== this) {
					return;
				}
				jQuery('.overlay-qr').removeClass('show');
			});
			jQuery('.overlay-qr').find('.cta').click(function () {
				jQuery('.overlay-qr').removeClass('show');
			});
			
			var catchline = ''
			if(productInfo.productInfo.designFor !== null) {
				catchline = productInfo.productInfo.designFor + '<br>';
			}
			jQuery('.product-detail').find('.product-content .description').html(catchline + productInfo.productInfo.longDescription);

			var benefits = '';
			jQuery.each(productInfo.productAdvantages, function (a, b) {
				if (typeof b.contentImage !== 'undefined') {
					benefits += '<div class="benefit"><div class="icon"><img src="' + b.contentImage + '" /></div><div class="text"><h3>' + b.contentTitle + '</h3><p>' + link2QR(b.contentValue) + '</p></div></div>';
				} else {
					benefits += '<div class="benefit"><div class="icon"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" /></div><div class="text"><h3>' + b.contentTitle + '</h3><p>' + link2QR(b.contentValue) + '</p></div></div>';
				}
			});
			jQuery('.product-detail').find('.product-content .benefits .content').html(benefits);

			var technical = '';
			jQuery.each(productInfo.productCharacteristic, function (a, b) {
				technical += '<div class="info"><div class="text"><h3>' + b.contentTitle + '</h3><p>' + link2QR(b.contentValue) + '</p></div></div>';
			});
			jQuery('.product-detail').find('.product-content .technical-infos .content').html(technical);


			if (productInfo.skus.length > 1) {
				var variant = '';
				jQuery.each(productInfo.skus, function (i, v) {
					variant += '<div class="variant" data-variant-id="' + v.id + '" data-online-quantities="' + v.availableQuantity + '" data-shipping-delay="' + v.shippingDelay + '">' + v.size + '</div>';
				});
				jQuery('.product-detail').find('.product-content .variants').html('<h4>' + lang.variants + '</h4><div class="list">' + variant + '</div></div>')

				jQuery('.variants').find('.list .variant').click(function () {
					jQuery('.variants').find('.list .variant').removeClass('selected');
					jQuery(this).addClass('selected');
					setAvailabilityInfo(ref, jQuery(this).attr('data-variant-id'));
				});
				jQuery('.variants').find('.list .variant').eq(0).trigger('click');
			} else {
				setAvailabilityInfo(ref, productInfo.skus[0].id);
			}
			
			if (price[0] > freeShippingThreshold && freeShippingThreshold !== false) {
				jQuery('.product-detail').find('.product-content .shipping-method.home span').html(lang.free);
			} else {
				jQuery('.product-detail').find('.product-content .shipping-method.home span').html(lang.from + ' ' + shippingFrom);
			}


			initSwiper(function () {
				jQuery('.product-detail').scrollTop(0);
				jQuery('.main-container').addClass('product-detail-open')
				setTimeout(function () {
					product.removeClass('active');
				}, 600)
			});
		});
	} else {
		jQuery('.main-container').removeClass('product-detail-open');
		jQuery('.overlay-qr').removeClass('show');
	}
}

jQuery(document).on('click', 'h1', function () {
	location.reload();
});

function initSwiper(callback) {
	var sliderMain = jQuery('.product-detail').find('.image-slider .main');
	var sliderNav = jQuery('.product-detail').find('.image-slider .nav');
	sliderMain.on('init', function (event, slick) {
		callback();
	});
	sliderMain.slick({
		dots: false,
		infinite: false,
		speed: 400,
		arrows: true,
		rows: 0,
		slidesToShow: 1,
		lazyLoad: 'ondemand',
		asNavFor: '.slider'
	})
	sliderNav.slick({
		dots: false,
		infinite: false,
		speed: 400,
		arrows: false,
		rows: 0,
		focusOnSelect: true,
		slidesToShow: 6,
		lazyLoad: 'ondemand',
		asNavFor: '.slider'
	});
}

function getAvailability(article, store, callback) {
	var url = applicationPath + 'func/getAvailability.php?storeId=' + store + '&articleId=' + article;
	jQuery.ajax({
		type: "GET",
		url: url,
		async: true,
		cache: false,
		content: 'application/x-json;charset=UTF-8',
		dataType: 'json',
		success: function (json) {
//			console.log(json);
			callback(json);
		}
	});
}

function setAvailabilityInfo(model, article) {
	var variant = productInfo.skus.filter(function (obj) {
		return obj.id === article;
	});
	if (variant[0].availableQuantity > 0) {
		if (variant[0].availableQuantity < 10) {
			jQuery('.availability').find('.online').removeClass().addClass('online available warning').html('<span class="icon icon-check"></span> ' + lang.less_than_available_online);
		} else {
			jQuery('.availability').find('.online').removeClass().addClass('online available').html('<span class="icon icon-check"></span> ' + lang.available_online);
		}
		jQuery('.delivery').find('.shipping-method.home, .shipping-method.click-collect').attr('style', '');
	} else {
		jQuery('.availability').find('.online').removeClass().addClass('online unavailable').html('<span class="icon icon-x"></span> ' + lang.not_available_online);
		jQuery('.availability').find('.cta').addClass('hide');
		jQuery('.delivery').find('.shipping-method.home, .shipping-method.click-collect').css('opacity', '.25');
	}

	getAvailability(article, jQuery('.main-container').attr('data-store-id'), function (data) {
		var stock = data.stock.stock;
		if (!jQuery('.availability').find('.online').hasClass('unavailable')) {
			jQuery('.availability').find('.cta').removeClass().addClass('cta right yellow shop-online').html(lang.order_online).attr({
				'data-model-id': model,
				'data-variant-id': article,
				'data-tid': 'order-online-' + article
			});
		}
		if (typeof stock !== 'undefined') {
			if (stock > 0) {
				jQuery('.availability').find('.instore').removeClass().addClass('instore available').html('<span class="icon icon-check"></span> ' + stock + ' ' + lang.available);
				jQuery('.availability').find('.cta').removeClass().addClass('cta right yellow').html(lang.take_now).attr({
					'data-model-id': model,
					'data-variant-id': article,
					'data-tid': 'take-away-' + article
				});
			} else {
				jQuery('.availability').find('.instore').removeClass().addClass('instore unavailable').html('<span class="icon icon-x"></span> ' + lang.temporarily_not_available);
			}
		} else {
			jQuery('.availability').find('.instore').removeClass().addClass('instore unavailable').html('<span class="icon icon-x"></span> ' + lang.not_available_in_this_store);
		}
	});

	var nearbyStores = jQuery('.shipping-method.click-collect-1h .nearby-stores').find('.store');
	var ct = 0;
	jQuery.each(nearbyStores, function (i, v) {
		getAvailability(article, nearbyStores.eq(i).attr('data-store-id'), function (data) {
			var stock = variant.availableQuantity;
			if (typeof stock !== 'undefined') {
				if (stock > 0) {
					nearbyStores.eq(i).attr('style', '').find('.availability').html('<span class="icon icon-check"></span>&nbsp;' + stock + ' ' + lang.immediately_available);
					jQuery('.delivery').find('.shipping-method.click-collect-1h').attr('style', '');
					nearbyStores.eq(i).find('.reserve-now .cta').attr({
						'data-model-id': model,
						'data-variant-id': article,
						'data-tid': 'reserve-now-' + nearbyStores.eq(i).attr('data-store-id') + '-' + article
					});
				} else {
					nearbyStores.eq(i).css('display', 'none');
					ct++;
				}
				jQuery('.reserve-now').find('.cta').click(function () {
					var storeId = jQuery(this).parents('.store').attr('data-store-id');
					var storeName = jQuery(this).parents('.store').attr('data-store-name');
					var url = 'https://content.decathlon.de/qr/store/' + jQuery('.main-container').attr('data-store-id') + '?ea=ebooking&aid=' + jQuery(this).attr('data-variant-id') + '&pid=' + jQuery(this).attr('data-model-id') + '&eo=' + jQuery('.main-container').attr('data-store-id') + '&et=' + storeId + '&t=' + Math.floor(Date.now() / 1000);
					openOverlayQR(url, lang.scan_qr_reserve.replace('%%STORE_NAME%%', storeName));
				});
			} else {
				nearbyStores.eq(i).css('display', 'none');
				ct++;
			}
			if (ct === nearbyStores.length) {
				jQuery('.delivery').find('.shipping-method.click-collect-1h').css('opacity', '.25');
			}
		});
	});
}

function openOverlayQR(url, headline) {
	makeCode(url, document.getElementById('qrcode-overlay'));
	jQuery('.overlay-qr').find('.headline').html(headline);
	jQuery('.overlay-qr').addClass('show');
}