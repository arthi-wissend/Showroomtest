
jQuery('.sport').click(function(e) {
	if(jQuery('select').find(':selected').attr('data-store-id')) {
		jQuery(e.target).addClass('selected');
		var selectedSport = jQuery(this).attr('data-global-name');
		
		jQuery.getJSON('cache/sports/' + selectedSport + '.json', function(data) {
			
			var ct = 0, list = '';
			jQuery.each(data['categories'], function(i,v) {
				var category = data['categories'][i]['translations'].find(x => x.language === locale.split('_')[0]).label;
				list += '<div class="list-item" data-category="' + i +'"><div class="item-content"><span class="order">' + (ct + 1) +'</span> ' + category +'<span class="icon icon-menu"></span></div></div>';
				ct++;
			});
			jQuery('.step-2').find('.category-list').html(list);
			
			jQuery('.step-1').css('display','none');
			jQuery('.step-2').css('display','block');
			
			window.csRowSize  				= 70;
			window.csContainer				= document.querySelector('.category-list');
			window.csContainer.style.height = ct * window.csRowSize;
			window.csListItems				= Array.from(document.querySelectorAll('.list-item')); // Array of elements
			window.csSortables				= window.csListItems.map(Sortable); // Array of sortables
			window.csTotal   				= window.csSortables.length;

			TweenLite.to(window.csContainer, 0.5, { autoAlpha: 1 });
			
			jQuery('#launchShowroom').click(function() {
				var csList 	= jQuery('.category-list').find('.list-item');
				var sorting = {};
				jQuery.each(csList, function(i,v) {
					sorting[i] = csList.eq(i).attr('data-category');
				});
				createCookie('ds_cat_sort', b64EncodeUnicode(JSON.stringify(sorting)));
				window.location.href = jQuery('select').find(':selected').attr('data-store-id') + '/' + selectedSport;
			});
			
			jQuery('#backToSport').click(function() {
				jQuery(e.target).removeClass('selected');
				jQuery('.step-2').css('display','none');
				jQuery('.step-1').css('display','block');
			});
		});
	} else {
		jQuery('select').css({'border':'1px solid red','color':'red'});
	}
	jQuery('select').on('change',function() {
		jQuery(this).attr('style','');
	});
});

function makeCode(value, target) {
	var qrcode = new QRCode(target, {
		useSVG: true,
		colorDark : "#ffffff",
		colorLight: "#0082c3"
	});
	qrcode.makeCode(value);
}

makeCode('https://plus.google.com/communities/112787125540482862872', document.getElementById('qr-code'));


function changeIndex(item, to) {
	// Change position in array
	arrayMove(window.csSortables, item.index, to);

	// Change element's position in DOM. Not always necessary. Just showing how.
	if (to === window.csTotal - 1) {
	window.csContainer.appendChild(item.element);    
	} else {    
	var i = item.index > to ? to : to + 1;
	window.csContainer.insertBefore(item.element, window.csContainer.children[i]);
	}    

	// Set index for each sortable
	window.csSortables.forEach((sortable, index) => sortable.setIndex(index));
}

function Sortable(element, index) {
	var content = element.querySelector(".item-content");
	var order   = element.querySelector(".order");

	var animation = TweenLite.to(content, 0.3, {
		boxShadow: "rgba(0,0,0,0.2) 0px 16px 32px 0px",
		force3D: true,
		scale: 1.1,
		paused: true
	});

	var dragger = new Draggable(element, {        
		onDragStart: downAction,
		onRelease: upAction,
		onDrag: dragAction,
		cursor: "inherit",    
		type: "y"
	});

	// Public properties and methods
	var sortable = {
		dragger:  dragger,
		element:  element,
		index:    index,
		setIndex: setIndex
	};

	TweenLite.set(element, { y: index * window.csRowSize });

	function setIndex(index) {

		sortable.index = index;    
		order.textContent = index + 1;

		// Don't layout if you're dragging
		if (!dragger.isDragging) layout();
	}

	function downAction() {
		animation.play();
		this.update();
	}

	function dragAction() {

		// Calculate the current index based on element's position
		var index = clamp(Math.round(this.y / window.csRowSize), 0, window.csTotal - 1);

		if (index !== sortable.index) {
		  changeIndex(sortable, index);
		}
	}

	function upAction() {
		animation.reverse();
		layout();
	}

	function layout() {    
		TweenLite.to(element, 0.3, { y: sortable.index * window.csRowSize });  
	}

	return sortable;
}

// Changes an elements's position in array
function arrayMove(array, from, to) {
  array.splice(to, 0, array.splice(from, 1)[0]);
}

// Clamps a value to a min/max
function clamp(value, a, b) {
  return value < a ? a : (value > b ? b : value);
}