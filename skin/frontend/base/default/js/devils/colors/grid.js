jQuery.noConflict();
var DevilsColors = {

	_levels: [],
	_selectedOptions: [],
	_map: null,
	_isInited: false,
	_config: null,
	_options: null,
	_timer: null,
	_imageDispatch: null,
	_priceDispatch: null,

	init: function(json, options){
		if(!this._isInited){
			this.getLevels()
				.initMap()
				.setupLevels();
			this._config = json;
			this._options = options;
			this._isInited = true;
		}
	},

	getLevels: function(){
		jQuery('.input-grid').each(function(){
			DevilsColors._levels.push(DevilsColors.getData(jQuery(this), 'super'));
		});
		return this;
	},

	getParentSelected: function($option){
		var superId = DevilsColors.getData($option.closest('.input-grid'), 'super');
		var prevSuperIndex = this._levels.indexOf(superId)-1;
		if(prevSuperIndex == -1){
			return true;
		}
		return jQuery('.input-grid[data-super="' + this._levels[prevSuperIndex] + '"] .option-box.selected').length;
	},

	setupLevels: function(){
		for(var i=1; i<this._levels.length; i++){
			if(this._levels[i] != undefined){
				jQuery('.input-grid[data-super="' + this._levels[i] + '"] .option-box').addClass('disabled');
			}
		}

		return this;
	},

	initDefault: function(){
		var $grid = jQuery('.input-grid').first();
		if($grid){
			if($grid.find('.option-box').length == 1){
				var $option = $grid.find('.option-box:not(.disabled)').first();
				DevilsColors.select($option);
			}
		}
	},

	initMap: function(){
		if(this._map == null){
			this._map = {};
		}else{
			return;
		}

		jQuery('.option-box').each(function(){
			var productIds = DevilsColors.getData(jQuery(this), 'products', true);
			if(productIds == undefined){
				productIds = DevilsColors.getData(jQuery(this), 'products', true);
			}
			for(var i=0; i<productIds.length; i++){
				if(!DevilsColors._map[productIds[i]]){
					DevilsColors._map[productIds[i]] = [];
				}
				DevilsColors._map[productIds[i]].push(jQuery(this).attr('id'));
			}
		});
		return this;
	},

	start: function(){
		jQuery('.input-grid[data-super="' + this._levels[0] + '"] .option-box').each(function(){
			if(!DevilsColors.getData(jQuery(this), 'products', true).length){
				jQuery(this).addClass('disabled').find('.qty-low').remove();
			}
		});

		var products = new Array();
		jQuery('.input-grid').find('.option-box').each(function(){
			var productArr = DevilsColors.getData(jQuery(this), 'products', true);
			products = products.concat(productArr);
		});

		if(this._levels.length == 1){
			this.refreshBubbles().refreshAvailable(products, -1);
		}
		this.initDefault();
		return this;
	},

	findObjectByKey: function(search, obj){
		var keyVal = search.split(':');
		for(var i in obj){
			if(obj[i][keyVal[0]] == keyVal[1]){
				return obj[i];
			}
		}
		return obj;
	},

	getConfigValue: function(xpath, searchObj){
		var path = xpath.toString().split('/');
		var obj;
		if(!searchObj){
			obj = this._config['attributes'][path[0]];
		}else{
			obj = searchObj[path[0]];
		}
		if(path.length > 1){
			for(var i=1; i<path.length; i++){
				if(obj == undefined){
					return false;
				}
				if(path[i].toString().indexOf(':') != -1){
					obj = this.findObjectByKey(path[i].toString(), obj);
				}else{
					obj = obj[path[i]];
				}
			}
		}
		return obj;
	},

	enableByProductId: function(productId, level){
		if(this._map[productId]){
			for(var i=0; i<this._map[productId].length; i++){
				if(this.isAtLevel(this._map[productId][i], level+1)){
					jQuery('#' + this._map[productId][i]).removeClass('disabled').off('hover');
				}
			}
		}
		return this;
	},

	isAtLevel: function(product, level){
		return DevilsColors.getData(jQuery('#' + product).closest('.input-grid'), 'super') == this._levels[level];
	},

	isLastLevel: function($option){
		return DevilsColors.getData($option.closest('.input-grid'), 'super') == this._levels[this._levels.length-1];
	},

	refreshStock: function(productId, level){
		if(!this._options['show_notice']){
			return this;
		}

		if(productId != undefined){
			var option = this._map[productId][level+1];

			/* May need the following, may not. We shall see, oui oui!

			productId = this.getCorrectProductId(jQuery('#' + option), productId);
			if(jQuery('#' + option + ' .qty-low').length && !this.isQtyLow(productId)){
				return this;
			}

			*/

			if((this.isQtyLow(productId) && !jQuery('#' + option).hasClass('disabled')) && this.isLastLevel(jQuery('#' + option))){
				if(!jQuery('#' + option + ' .qty-low').length){
					jQuery('#' + option).append('<span class="qty-low">' + this._options['notice_msg'].replace('%q', this.getConfigValue(productId + '/qty', this._options)) + '</span>');
					jQuery('#' + option).find('.qty-low').hide().slideDown('fast');
				}else{
					jQuery('#' + option + ' .qty-low').html(this.getConfigValue(productId + '/qty', this._options) + ' left!');
				}
			}else{
				if(jQuery('#' + option + ' .qty-low').length){
					jQuery('#' + option + ' .qty-low').slideUp('fast', function(){
						jQuery(this).remove();
					});
				}
			}
		}
		return this;
	},

	refreshAvailable: function(productIds, level){
		for(var i=0; i<productIds.length; i++){
			if(this.getConfigValue(productIds[i] + '/qty', this._options) > 0){
				this.enableByProductId(productIds[i], level).refreshStock(productIds[i], level);
			}
		}

		jQuery('.option-box.disabled').each(function(){
			jQuery(this).find('.qty-low').slideUp('fast', function(){
				jQuery(this).remove();
			});
		});
		return this;
	},

	getCorrectProductId: function($option, productId){
		try
		{
			var allProducts = new Array();
			allProducts.push(DevilsColors.getData($option, 'products', true));
			var parentSuper = DevilsColors.getData($option.closest('.input-grid'), 'super');
			var level = this._levels.indexOf(parentSuper);
			for(var i=level-1; i>=0; i--){
				var products = DevilsColors.getData(jQuery('.input-grid[data-super="' + this._levels[i] + '"] .option-box.selected'), 'products', true);
				allProducts.push(products);
			}

			if(allProducts.length == 1 && allProducts[0].length == 1){
				return allProducts[0][0];
			}
			var common = this.getCommonDenomenator(allProducts);
			if(common === false){
				return productId;
			}

			//if(common.length>1){
			//	common = this.isLastLevel($option) ? this.getLowerQty(common) : this.getGreaterQty(common);
			//}
			return common;
		}catch(e){
			return productId;
		}
	},

	getCommonDenomenator: function(arr){
		var count = arr.length;
		var hitCount = {};
		for(var i=0; i<arr.length; i++){
			if(arr[i] != undefined && arr[i].length){
				for(var j=0; j<arr[i].length; j++){
					if(!hitCount[arr[i][j]]){
						hitCount[arr[i][j]] = 1;
					}else{
						hitCount[arr[i][j]]++;
					}
				}
			}
		}

		var matched = new Array();
		for(var i in hitCount){
			if(hitCount[i] == count){
				matched.push(i);
			}
		}

		if(matched.length > 1){
			return matched;
		}else if(matched.length == 1){
			return matched[0];
		}

		return false;
	},

	getGreaterQty: function(common){
		var highest = -1;
		var productId = 0;
		for(var i=0; i<common.length; i++){
			var qty = this.getConfigValue(common[i] + '/qty', this._options);
			if(qty > highest){
				highest = qty;
				productId = common[i];
			}
		}
		return productId;
	},

	getLowerQty: function(common){
		var lowest = 9999999999;
		var productId = 0;
		for(var i=0; i<common.length; i++){
			var qty = this.getConfigValue(common[i] + '/qty', this._options);
			if(qty < lowest){
				lowest = qty;
				productId = common[i];
			}
		}
		return productId;
	},

	isQtyLow: function(productId, $option){
		if(productId != undefined && productId.length){
			for(var i=0; i<productId.length; i++){
				if(this.getConfigValue(productId[i] + '/isLow', this._options)){
					return true;
				}
			}
		}

		return this.getConfigValue(productId + '/isLow', this._options);
	},

	getId: function(){
		var allIds = [];
		jQuery('.input-grid .option-box.selected').each(function(){
			var $option = jQuery(this);
			allIds.push(DevilsColors.getData($option, 'products', true));
		});

		var filterBy = null;
		var candidates = [];
		for(i=0; i<allIds.length; i++){
			if(filterBy == null){
				filterBy = allIds[i];
			}else{
				for(var j=0; j<allIds[i].length; j++){
					var index = jQuery.inArray(allIds[i][j], filterBy);
					if(index != -1 && !isNaN(allIds[i][j])){
						if(jQuery.inArray(allIds[i][j], candidates) == -1){
							candidates.push(allIds[i][j]);
						}
					}
				}
			}
		}

		if(candidates.length){
			return candidates[0];
		}
		return false;
	},

	select: function($option){
		var superId = DevilsColors.getData($option.closest('.input-grid'), 'super');
		var productId = this.getCorrectProductId($option, false);
		var option = DevilsColors.getData($option, 'id');
		var title = $option.find('a').attr('title');

		var $dt = $option.closest('dd').prev('dt');
		var text = $dt.find('label').text().replace(/:.*$/, '');

		if(text.indexOf('*') != -1){
			text = text.replace('*', '');
			$dt.find('label').html('<em>*</em>' + text + ': ' + '<span>' + title + '</span>' );
		}else{
			$dt.find('label').html(text + ': ' + '<span>' + title + '</span>' );
		}

		jQuery('.input-grid[data-super="' + superId + '"]').find('.option-box').removeClass('selected');

		$option.addClass('selected');


		if(this.isLastLevel($option)){
			var basePrice = parseFloat(this.getConfigValue('basePrice', this._config));
			var addPrice = parseFloat(this.getConfigValue(superId + '/options/id:' + option + '/price'));
			var itemPrice = basePrice+addPrice;

			var image = this.getConfigValue(productId + '/image', this._options);
			var largeImage = this.getConfigValue(productId + '/large_image', this._options);

			if(image && largeImage && typeof this._imageDispatch == 'function' && image.indexOf('/placeholder') == -1 && largeImage.indexOf('/placeholder') == -1){
				this._imageDispatch(largeImage, image);
			}

			if(itemPrice && typeof this._priceDispatch == 'function'){
				this._priceDispatch(this.formatCurrency(itemPrice));
			}
		}

		this.clearBySuper(superId).refreshAvailable(DevilsColors.getData($option, 'products', true), DevilsColors._levels.indexOf(superId));
		this.refreshBubbles();
		jQuery('#attribute' + superId).removeAttr('disabled').val(option);
		var level = DevilsColors._levels.indexOf(superId);
		if(DevilsColors._levels[level+1] != undefined){
			if(jQuery('.input-grid:eq(' + (level+1) + ')').find('.option-box:not(.disabled)').length == 1){
				var $nextOption = jQuery('.input-grid:eq(' + (level+1) + ')').find('.option-box:not(.disabled)').first();
				if(!$nextOption.hasClass('disabled')){
					DevilsColors.select($nextOption);
				}
			}
		}
	},

	getData: function($el, value, asArray){
		if(!$el.length){
			return (asArray ? [] : '');
		}
		if($el.data(value)){
			if(asArray && !$el.data(value).length){
				return [$el.data(value)];
			}
			return $el.data(value);
		}
		var data = $el.attr('data-' + value).replace('[', '').replace(']', '');
		return (asArray == true ? data.split(',') : data);
	},

	clearBySuper: function(superId){
		var start = this._levels.indexOf(superId);
		for(var i=start+1; i<this._levels.length; i++){
			jQuery('#attribute' + this._levels[i]).val('');
			jQuery.map(jQuery('.input-grid[data-super="' + this._levels[i] + '"] .option-box'), function(n){
				jQuery(n).removeClass('selected').addClass('disabled');
			});
		}
		return this;
	},

	stockAlert: function($option){
		var superId = $option.closest('.input-grid').data('super');
		if(superId != this._levels[this._levels.length-1]){
			return;
		}
		for(var i=0; i<this._levels.length-1; i++){
			if(jQuery('#attribute' + this._levels[i]).val() == ''){
				return;
			}
		}

		jQuery('.bubble').remove();
		if(!this._options['logged_in']){
			this.login($option);
		}else{
			var $label = $option.find('.label');
			var labelTxt = $label.html();
			var oldStyle = $label.attr('style');
			$label.html('').attr('style', '').addClass('loading');
			var postData = { 'product_id': this._options['product_id'] };

			var superId = $option.closest('.input-grid').data('super');
			var optionId = $option.data('id');
			postData[superId] = optionId;

			jQuery('.option-box.selected').each(function(){
				superId = jQuery(this).closest('.input-grid').data('super');
				optionId = jQuery(this).data('id');
				postData[superId] = optionId;
			});

			superId = $option.closest('.input-grid').data('super');
			optionId = $option.data('id');
			postData[superId] = optionId;

			jQuery.ajax({
				url : '/configgrid/index/stock',
				dataType : 'json',
				data : postData,
				complete : function(data){
					var response = jQuery.parseJSON(data.responseText);
					$label.removeClass('loading').html(labelTxt).attr('style', oldStyle);
					DevilsColors.bubble($option, response.message, false);
				}
			});
		}
	},

	bubble: function($obj, message, hover){
		jQuery('.input-grid .bubble').remove();
		var bubbleClass = 'bubble' + $obj.data('id');
		if(!hover){
			clearTimeout(this._timer);
			$obj.parent().append('<div class="' + bubbleClass + ' bubble sticky">' + message + '</div>');
			var offsetLeft = ($obj.position().left+Math.round(($obj.outerWidth()-$obj.parent().find('.' + bubbleClass).outerWidth())/2));
			var offsetTop = $obj.position().top-($obj.parent().find('.' + bubbleClass).outerHeight()+6);
			$obj.parent().find('.' + bubbleClass + '.sticky').css({ left: offsetLeft, top: offsetTop });

			/* Force the correct width, if we hit the edge of the parent container the bubble will not size properly */
			var bubbleBottom = $obj.parent().find('.' + bubbleClass + '.sticky').position().top+($obj.parent().find('.' + bubbleClass + '.sticky').outerHeight()+6);
			var bubbleWidth = $obj.parent().find('.' + bubbleClass + '.sticky').width();

			while(bubbleBottom > $obj.position().top){
				bubbleWidth++;
				$obj.parent().find('.' + bubbleClass + '.sticky').width(bubbleWidth);
				bubbleBottom = $obj.parent().find('.' + bubbleClass).position().top+($obj.parent().find('.' + bubbleClass + '.sticky').outerHeight()+6);
			}
			$obj.parent().find('.' + bubbleClass + '.sticky').width(bubbleWidth+1);

			/* Re-center the bubble once more, in case the width changed */
			offsetLeft = ($obj.position().left+Math.round(($obj.outerWidth()-$obj.parent().find('.' + bubbleClass + '.sticky').outerWidth())/2));
			offsetTop = $obj.position().top-($obj.parent().find('.' + bubbleClass + '.sticky').outerHeight()+6);
			$obj.parent().find('.' + bubbleClass + '.sticky').css({
				left: offsetLeft,
				top: offsetTop
			});
			$obj.parent().find('.' + bubbleClass + '.sticky').hide().fadeIn(200);

			this._timer = setTimeout(function(){
				$obj.parent().find('.' + bubbleClass + '.sticky').hide().fadeOut(200, function(){
					jQuery(this).remove();
				});
			}, 5000);
		}else{
			$obj.hoverIntent(
				function(){
					if(!$obj.parent().find('.' + bubbleClass + '.sticky').length){
						$obj.parent().append('<div class="' + bubbleClass + ' bubble">' + message + '</div>');
						
						var offsetLeft = ($obj.position().left+Math.round(($obj.outerWidth()-$obj.parent().find('.' + bubbleClass).outerWidth())/2));
						var offsetTop = $obj.position().top-($obj.parent().find('.' + bubbleClass).outerHeight()+6);
						$obj.parent().find('.' + bubbleClass).css({
							left: offsetLeft,
							top: offsetTop
						});

						/* Force the correct width, if we hit the edge of the parent container the bubble will not size properly */
						var bubbleBottom = $obj.parent().find('.' + bubbleClass).position().top+($obj.parent().find('.' + bubbleClass).outerHeight()+6);
						var bubbleWidth = $obj.parent().find('.' + bubbleClass).width();

						
						while(bubbleBottom > $obj.position().top){
							$obj.parent().find('.' + bubbleClass).width(bubbleWidth);
							bubbleBottom = $obj.parent().find('.' + bubbleClass).position().top+($obj.parent().find('.' + bubbleClass).outerHeight()+6);
							bubbleWidth++;
						}
						$obj.parent().find('.' + bubbleClass).width(bubbleWidth+1);
						

						/* Re-center the bubble once more, in case the width changed */
						offsetLeft = ($obj.position().left+Math.round(($obj.outerWidth()-$obj.parent().find('.' + bubbleClass).outerWidth())/2));
						offsetTop = $obj.position().top-($obj.parent().find('.' + bubbleClass).outerHeight()+6);
						$obj.parent().find('.' + bubbleClass).css({
							left: offsetLeft,
							top: offsetTop
						});
						
						$obj.parent().find('.' + bubbleClass).hide().fadeIn(200);
					}
				},
				function(){
					$obj.parent().find('.' + bubbleClass + ':not(.sticky)').fadeOut(200, function(){
						jQuery(this).remove();
					});
				}
			);
		}
	},

	refreshBubbles: function(){
		jQuery('.option-box').each(function(){
			var $this = jQuery(this);
			$this.off('hover');
			if(DevilsColors.isLastLevel($this)){
				if($this.hasClass('disabled')){
					if(DevilsColors.getParentSelected($this)){
						DevilsColors.bubble($this, 'Click to receive an email when this product comes back in stock.', true);
					}
				}else{
					var superId = $this.closest('.input-grid').data('super');
					var optionId = $this.data('id');
					var price = DevilsColors.getConfigValue(superId + '/options/id:' + optionId + '/price');
					if(price > 0){
						DevilsColors.bubble($this, 'add ' + DevilsColors.formatCurrency(price), true);
					}
				}
			}
		});
		return this;
	},

	toggleLogin: function(open){
		if(open == true){
			if(jQuery('.ajax-login')){
				jQuery('.ajax-login').appendTo('body');
			}
			jQuery('.ajax-login .close').click(function(){
				DevilsColors.toggleLogin();
			});
			jQuery('.ajax-login').css({
				display: 'block',
				width: 0,
				height: 0,
				top: ((jQuery(window).height() / 2) + jQuery(window).scrollTop()),
        		left: ((jQuery(window).width() / 2) + jQuery(window).scrollLeft()),
        		opacity: 0
			}).find('.form-list').hide();

			jQuery('.ajax-login').animate({
				width: 300,
				height: 185,
				top: (((jQuery(window).height() - 200) / 2) + jQuery(window).scrollTop()),
        		left: (((jQuery(window).width() - 320) / 2) + jQuery(window).scrollLeft()),
        		opacity: 1
			}, 500,
			function(){
				jQuery('.form-list').fadeIn('fast');
			});
		}else{
			jQuery('.form-list').fadeOut('fast');
			jQuery('.ajax-login').animate({
				width: 0,
				height: 0,
				top: ((jQuery(window).height() / 2) + jQuery(window).scrollTop()),
        		left: ((jQuery(window).width() / 2) + jQuery(window).scrollLeft()),
        		opacity: 0
			}, 500);
		}
	},

	login: function($option){
		this.toggleLogin(true);
		jQuery('#login-btn').click(function(){
			jQuery('.form-list li.error p').fadeOut(200, function(){
				jQuery(this).parent().addClass('loading');
			});
			jQuery.ajax({
				url : '/configgrid/index/login',
				data : { 'login': { 'username': jQuery('#email').val(), 'password': jQuery('#pass').val() } },
				dataType : 'json',
				type : 'POST',
				complete: function(data){
					var response = jQuery.parseJSON(data.responseText);
					DevilsColors._options['logged_in'] = response.success;
					if(response.success == true){
						DevilsColors.toggleLogin();
						DevilsColors.stockAlert($option);
					}else{
						jQuery('.form-list li.error').removeClass('loading');
						jQuery('.form-list li.error p').html(response.message).fadeIn(200);
					}
				}
			});
		});
	},

	formatCurrency: function(num){
		num = num.toString().replace(/\$|\,/g,'');
		if(isNaN(num)){
			num = "0";
		}
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num%100;
		num = Math.floor(num/100).toString();
		if(cents<10){
			cents = "0" + cents;
		}
		for(var i=0; i < Math.floor((num.length-(1+i))/3); i++){
			num = num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));
		}
		return (((sign) ? '' : '-') + this.getConfigValue('symbol', this._options) + num + '.' + cents);
	},

	onImageChange: function(evt){
		this._imageDispatch = evt;
	},

	onPriceChange: function(evt){
		this._priceDispatch = evt;
	}
};

jQuery(document).ready(function($){
	DevilsColors.start();
	$('.option-box').click(function(){
		if(!$(this).hasClass('disabled')){
			DevilsColors.select($(this));
		}else{
			DevilsColors.stockAlert($(this));
		}
	});
});