/**
 * jQuery List Rotator 
 * Copyright (c) 2012 Allan Ma (http://codecanyon.net/user/webtako)
 * Version: 1.2 (01/17/2012)
 */
;(function($) {
	var LEFT = "left";
	var RIGHT = "right";
	var PREV = 0;
	var NEXT = 1;
	var AUTO_ADJUST = 	 "auto_adjust";
	var UPDATE_TEXT = 	 "update_text";
	var UPDATE_BUTTONS = "update_buttons";		
	var UPDATE_NUMBER =  "update_number";
	var SHOW_SCROLLBAR = "show_scrollbar";
	var HIDE_SCROLLBAR = "hide_scrollbar";
	var MOVE_KNOB = 	 "update_knob";
	
	var ALIGN = {"TL":0, "TC":1, "TR":2, "BL":3, "BC":4, "BR":5};
	
	var ei = 0;
	var EFFECTS = {
		"block.top":ei++,
		"block.right":ei++,
		"block.bottom":ei++,
		"block.left":ei++,		
		"block.drop":ei++,		
		"diag.fade":ei++,
		"diag.exp":ei++,		
		"rev.diag.fade":ei++,
		"rev.diag.exp":ei++,		
		"block.fade":ei++,
		"block.exp":ei++,
		"block.top.zz":ei++,
		"block.bottom.zz":ei++,
		"block.left.zz":ei++,
		"block.right.zz":ei++,		
		"spiral.in":ei++,	
		"spiral.out":ei++,
		"vert.tl":ei++,
		"vert.tr":ei++,
		"vert.bl":ei++,
		"vert.br":ei++,		
		"fade.left":ei++,	
		"fade.right":ei++,		
		"alt.left":ei++,
		"alt.right":ei++,
		"blinds.left":ei++,
		"blinds.right":ei++,		
		"horz.tl":ei++,
		"horz.tr":ei++,		
		"horz.bl":ei++,
		"horz.br":ei++,		
		"fade.top":ei++,
		"fade.bottom":ei++,
		"alt.top":ei++,
		"alt.bottom":ei++,
		"blinds.top":ei++,
		"blinds.bottom":ei++,				
		"none":ei++,
		"fade":ei++,
		"h.slide":ei++,
		"v.slide":ei++,
		"random":ei++
	};
	
	var TEXT_EFFECTS = {"fade":0, "down":1, "right":2, "up":3, "left":4, "none":5}
	
	var LIMIT = 250;
	var BLOCK_SIZE = 75;
	var STRIPE_SIZE = 50;
	var DEFAULT_DELAY = 5000;
	var DURATION = 800;
	var DEFAULT_SPEED = 300;
	var TEXT_SPEED = 600;
	var SCROLL_DELAY = 0.1;
	var SCROLL_RATE = 4;
	var MAX_SCROLL_SPEED = 600;
	var SWIPE_MIN = 50;
	
	//Vertical Stripes Class
	function VertStripes(rotator) {
		this._$stripes;
		this._total;
		this._intervalId = null;
		this._rotator = rotator;
		this._areaWidth = rotator._screenWidth;
		this._areaHeight = rotator._screenHeight;
		this._size = rotator._vSize;
		this._duration = rotator._duration;
		this._delay = rotator._vDelay;
		
		this._total = Math.ceil(this._areaWidth/this._size);
		if (this._total > LIMIT) {
			this._size = Math.ceil(this._areaWidth/LIMIT);
			this._total = Math.ceil(this._areaWidth/this._size);
		}
		var divs = "";
		for (var i = 0; i < this._total; i++) {
			divs += "<div class='vpiece' id='" + i + "' style='left:" + (i * this._size) + "px; height:" + this._areaHeight + "px'></div>";
		}					
		this._rotator.addToScreen(divs);
		
		this._$stripes = this._rotator._$obj.find("div.vpiece");
	}

	//clear animation
	VertStripes.prototype.clear = function() {
		clearInterval(this._intervalId);
		this._$stripes.stop(true).css({"z-index":2, opacity:0});
	}

	//display content
	VertStripes.prototype.displayContent = function($img, effect) {
		this.setPieces($img, effect);
		this.animate($img, effect);
	}			
	
	//set image stripes
	VertStripes.prototype.setPieces = function($img, effect) {
		switch (effect) {
			case EFFECTS["vert.tl"]:
			case EFFECTS["vert.tr"]:
				this.setVertPieces($img, -this._areaHeight, 1, this._size, false);	
				break;
			case EFFECTS["vert.bl"]:
			case EFFECTS["vert.br"]:
				this.setVertPieces($img, this._areaHeight, 1, this._size, false);
				break;
			case EFFECTS["alt.left"]:
			case EFFECTS["alt.right"]:
				this.setVertPieces($img, 0, 1, this._size, true);
				break;
			case EFFECTS["blinds.left"]:
			case EFFECTS["blinds.right"]:
				this.setVertPieces($img, 0, 1, 0, false);
				break;
			default:
				this.setVertPieces($img, 0, 0, this._size, false);
		}
	}
	
	//set vertical stripes
	VertStripes.prototype.setVertPieces = function($img, topPos, opacity, width, alt) {
		var imgSrc = $img.attr("src");
		var tOffset = 0;
		var lOffset = 0;
		if (this._rotator._autoCenter) {
			tOffset = (this._areaHeight - $img.height())/2;
			lOffset = (this._areaWidth - $img.width())/2;
		}
		for (var i = 0; i < this._total; i++) {		
			var xPos =  ((-i * this._size) + lOffset);
			if (alt) {
				topPos = (i % 2) == 0 ? -this._areaHeight: this._areaHeight;
			}
			this._$stripes.eq(i).css({background:"url('"+ imgSrc +"') no-repeat", backgroundPosition:xPos + "px " + tOffset + "px",						   
									opacity:opacity, top:topPos, width:width, "z-index":3});					
		}
	}
	
	//animate stripes			
	VertStripes.prototype.animate = function($img, effect) {
		var start, end, incr, limit;
		var that = this;		
		switch (effect) {
			case EFFECTS["vert.tl"]:   case EFFECTS["vert.bl"]: 
			case EFFECTS["fade.left"]: case EFFECTS["blinds.left"]: 
			case EFFECTS["alt.left"]:
				start = 0;
				end = this._total - 1;
				incr = 1;
				break;
			default:
				start = this._total - 1;
				end = 0;
				incr = -1;
		}
		
		this._intervalId = setInterval(
			function() {
				that._$stripes.eq(start).animate({top:0, opacity:1, width:that._size}, that._duration, that._rotator._easing,
					function() {
						if ($(this).attr("id") == end) {
							that._rotator.showContent($img);
						}
					}
				);
				if (start == end) {
					clearInterval(that._intervalId);
				}
				start += incr;
			}, this._delay);						
	}
		
	//Horizontal Stripes Class
	function HorzStripes(rotator) {
		this._$stripes;
		this._total;
		this._intervalId = null;
		this._rotator = rotator;
		this._areaWidth = rotator._screenWidth;
		this._areaHeight = rotator._screenHeight;
		this._size = rotator._hSize;
		this._duration = rotator._duration;
		this._delay = rotator._hDelay;
		
		this._total = Math.ceil(this._areaHeight/this._size);
		if (this._total > LIMIT) {
			this._size = Math.ceil(this._areaHeight/LIMIT);
			this._total = Math.ceil(this._areaHeight/this._size);
		}
		var divs = "";
		for (var j = 0; j < this._total; j++) {
			divs += "<div class='hpiece' id='" + j + "' style='top:" + (j * this._size) + "px; width:" + this._areaWidth + "px'><!-- --></div>";
		}				
		this._rotator.addToScreen(divs);
		this._$stripes = this._rotator._$obj.find("div.hpiece");
	}

	//clear animation
	HorzStripes.prototype.clear = function() {
		clearInterval(this._intervalId);
		this._$stripes.stop(true).css({"z-index":2, opacity:0});
	}

	//display content
	HorzStripes.prototype.displayContent = function($img, effect) {
		this.setPieces($img, effect);
		this.animate($img, effect);
	}			
	
	//set image stripes
	HorzStripes.prototype.setPieces = function($img, effect) {
		switch (effect) {
			case EFFECTS["horz.tr"]:
			case EFFECTS["horz.br"]:
				this.setHorzPieces($img, this._areaWidth, 1, this._size, false);	
				break;
			case EFFECTS["horz.tl"]:
			case EFFECTS["horz.bl"]:
				this.setHorzPieces($img, -this._areaWidth, 1, this._size, false);
				break;
			case EFFECTS["alt.top"]:
			case EFFECTS["alt.bottom"]:
				this.setHorzPieces($img, 0, 1, this._size, true);
				break;
			case EFFECTS["blinds.top"]:
			case EFFECTS["blinds.bottom"]:
				this.setHorzPieces($img, 0, 1, 0, false);
				break;
			default:
				this.setHorzPieces($img, 0, 0, this._size, false);	
		}
	}
	
	//set horizontal stripes
	HorzStripes.prototype.setHorzPieces = function($img, leftPos, opacity, height, alt) {
		var imgSrc = $img.attr("src");
		var tOffset = 0;
		var lOffset = 0;
		if (this._rotator._autoCenter) {
			tOffset = (this._areaHeight - $img.height())/2;
			lOffset = (this._areaWidth - $img.width())/2;
		}
		for (var i = 0; i < this._total; i++) {			
			var yPos = ((-i * this._size) + tOffset);
			if (alt) {
				leftPos = (i % 2) == 0 ? -this._areaWidth: this._areaWidth;
			}
			this._$stripes.eq(i).css({background:"url('"+ imgSrc +"') no-repeat", backgroundPosition:lOffset + "px " + yPos + "px",
									opacity:opacity, left:leftPos, height:height, "z-index":3});		  
		}
	}
	
	//animate stripes			
	HorzStripes.prototype.animate = function($img, effect) {
		var start, end, incr;
		var that = this;
		switch (effect) {
			case EFFECTS["horz.tl"]:  case EFFECTS["horz.tr"]: 
			case EFFECTS["fade.top"]: case EFFECTS["blinds.top"]: 
			case EFFECTS["alt.top"]:
				start = 0;
				end = this._total - 1;
				incr = 1;
				break;
			default:
				start = this._total - 1;
				end = 0;
				incr = -1;

		}
		
		this._intervalId = setInterval(
			function() {
				that._$stripes.eq(start).animate({left:0, opacity:1, height:that._size}, that._duration, that._rotator._easing,
					function() {
						if ($(this).attr("id") == end) {
							that._rotator.showContent($img);
						}
					}
				);					
				if (start == end) {
					clearInterval(that._intervalId);
				}
				start += incr;
			}, this._delay);						
	}
		
	//class Blocks
	function Blocks(rotator) {
		this._$blockArr;
		this._$blocks;
		this._$arr;
		this._numRows;
		this._numCols;
		this._total;
		this._intervalId;
		this._rotator = rotator;
		this._areaWidth = rotator._screenWidth;
		this._areaHeight = rotator._screenHeight;
		this._size = rotator._bSize;
		this._duration = rotator._duration;
		this._delay = rotator._bDelay;
		
		this._numRows = Math.ceil(this._areaHeight/this._size);
		this._numCols = Math.ceil(this._areaWidth/this._size);			
		this._total = this._numRows * this._numCols;
		if (this._total > LIMIT) {
			this._size = Math.ceil(Math.sqrt((this._areaHeight * this._areaWidth)/LIMIT));
			this._numRows = Math.ceil(this._areaHeight/this._size);
			this._numCols = Math.ceil(this._areaWidth/this._size);			
			this._total = this._numRows * this._numCols;
		}
		var divs = "";							
		for (var i = 0; i < this._numRows; i++) {					
			for (var j = 0; j < this._numCols; j++) {
				divs += "<div class='block' id='" + i + "-" + j + "'></div>";	
			}
		}
		this._rotator.addToScreen(divs);
		this._$blocks = this._rotator._$obj.find("div.block");
		this._$blocks.data({tlId:"0-0", trId:"0-"+(this._numCols - 1), blId:(this._numRows - 1)+"-0", brId:(this._numRows - 1)+"-"+(this._numCols - 1)});
		
		var k = 0;
		this._$arr = new Array(this._total);
		this._$blockArr = new Array(this._numRows);
		for (var i = 0; i < this._numRows; i++) {
			this._$blockArr[i] = new Array(this._numCols);
			for (var j = 0; j < this._numCols; j++) {
				this._$blockArr[i][j] = this._$arr[k++] = this._$blocks.filter("#" + (i + "-" + j)).data("top", i * this._size);
			}
		}		
	}
	
	//clear blocks
	Blocks.prototype.clear = function() {
		clearInterval(this._intervalId);
		this._$blocks.stop(true).css({"z-index":2, opacity:0});
	}
	
	//display content
	Blocks.prototype.displayContent = function($img, effect) {
		switch (effect) {
			case EFFECTS["diag.fade"]:
				this.setBlocks($img, 0, this._size, 0);
				this.diagAnimate($img, {opacity:1}, false);	
				break;
			case EFFECTS["diag.exp"]:
				this.setBlocks($img, 0, 0, 0);
				this.diagAnimate($img, {opacity:1, width:this._size, height:this._size}, false);
				break;
			case EFFECTS["rev.diag.fade"]:
				this.setBlocks($img, 0, this._size, 0);
				this.diagAnimate($img, {opacity:1}, true);
				break;
			case EFFECTS["rev.diag.exp"]:
				this.setBlocks($img, 0, 0, 0);
				this.diagAnimate($img, {opacity:1, width:this._size, height:this._size}, true);
				break;
			case EFFECTS["block.fade"]:
				this.setBlocks($img, 0, this._size, 0);
				this.randomAnimate($img);
				break;
			case EFFECTS["block.exp"]:
				this.setBlocks($img, 1, 0, 0);
				this.randomAnimate($img);
				break; 
			case EFFECTS["block.drop"]:
				this.setBlocks($img, 1, this._size, -(this._numRows * this._size));
				this.randomAnimate($img);
				break;
			case EFFECTS["block.top.zz"]: 
			case EFFECTS["block.bottom.zz"]:					
				this.setBlocks($img, 0, this._size, 0);
				this.zigZag($img, effect);
				break;
			case EFFECTS["block.left.zz"]: 
			case EFFECTS["block.right.zz"]:
				this.setBlocks($img, 0, this._size, 0);
				this.zigZag($img, effect);
				break;
			case EFFECTS["spiral.in"]:
				this.setBlocks($img, 0, this._size, 0);
				this.spiral($img, false);
				break;
			case EFFECTS["spiral.out"]:
				this.setBlocks($img, 0, this._size, 0);
				this.spiral($img, true);
				break;
			default:
				this.setBlocks($img, 1, 0, 0);
				this.dirAnimate($img, effect);				
		}
	}
	
	//set blocks 
	Blocks.prototype.setBlocks = function($img, opacity, size, tPos) {
		var tOffset = 0;
		var lOffset = 0;
		if (this._rotator._autoCenter) {
			tOffset = (this._areaHeight - $img.height())/2;
			lOffset = (this._areaWidth - $img.width())/2;
		}
		var imgSrc = $img.attr("src");
		for (var i = 0; i < this._numRows; i++) {							
			for (var j = 0; j < this._numCols; j++) {
				var tVal = ((-i * this._size) + tOffset);
				var lVal = ((-j * this._size) + lOffset);
				this._$blockArr[i][j].css({background:"url('"+ imgSrc +"') no-repeat", backgroundPosition:lVal + "px " + tVal + "px",
									 opacity:opacity, top:(i * this._size) + tPos, left:(j * this._size), width:size, height:size, "z-index":3});
			}					
		}
	}
	
	//diagonal effect
	Blocks.prototype.diagAnimate = function($img, props, rev) {
		var that = this;
		var $array = new Array(this._total);
		var start, end, incr, lastId;
		var diagSpan = (this._numRows - 1) + (this._numCols - 1);
		if (rev) {				
			start = diagSpan;
			end = -1;
			incr = -1;
			lastId = this._$blocks.data("tlId");
		}
		else {
			start = 0;
			end = diagSpan + 1;
			incr = 1;
			lastId = this._$blocks.data("brId");
		}
		
		var count = 0;
		while (start != end) {
			i = Math.min(this._numRows - 1, start);
			while(i >= 0) {			
				j = Math.abs(i - start);
				if (j >= this._numCols) {
					break;
				}
				$array[count++] = this._$blockArr[i][j];
				i--;
			}
			start+=incr;
		}
		
		count = 0;
		this._intervalId = setInterval(
			function() {
				$array[count++].animate(props, that._duration, that._rotator._easing,
						function() {
							if ($(this).attr("id") == lastId) {
								that._rotator.showContent($img);
							}
						});						
				if (count == that._total) {
					clearInterval(that._intervalId);
				}			
			}, this._delay);			
	}

	//zig zag effect
	Blocks.prototype.zigZag = function($img, effect) {
		var that = this;
		var fwd = true;
		var i = 0, j = 0, incr, lastId, horz;
		if (effect == EFFECTS["block.left.zz"]) {
			lastId = (this._numCols%2 == 0) ? this._$blocks.data("trId") : this._$blocks.data("brId");
			j = 0;
			incr = 1;
			horz = false;
		}
		else if (effect == EFFECTS["block.right.zz"]) {
			lastId = (this._numCols%2 == 0) ? this._$blocks.data("tlId") : this._$blocks.data("blId");
			j = this._numCols - 1;
			incr = -1;
			horz = false;
		}
		else if (effect == EFFECTS["block.top.zz"]) {
			lastId = (this._numRows%2 == 0) ? this._$blocks.data("blId") : this._$blocks.data("brId");
			i = 0;
			incr = 1;
			horz = true;
		}
		else {
			lastId = (this._numRows%2 == 0) ? this._$blocks.data("tlId") : this._$blocks.data("trId");
			i = this._numRows - 1;
			incr = -1;
			horz = true;
		}
		
		this._intervalId = setInterval(
			function() {
				that._$blockArr[i][j].animate({opacity:1}, that._duration, that._rotator._easing,
						function() {
							if ($(this).attr("id") == lastId) {
								that._rotator.showContent($img);
							}});
				
				if (that._$blockArr[i][j].attr("id") == lastId) {
					clearInterval(that._intervalId);
				}
				
				if (horz) {
					(fwd ? j++ : j--);
					if (j == that._numCols || j < 0) {
						fwd = !fwd;
						j = (fwd ? 0 : that._numCols - 1);
						i+=incr;
					}						
				}
				else {
					(fwd ? i++ : i--);
					if (i == that._numRows || i < 0) {
						fwd = !fwd;
						i = (fwd ? 0 : that._numRows - 1);
						j+=incr;
					}
				}
			}, this._delay);
	}
	
	//vertical direction effect
	Blocks.prototype.dirAnimate = function($img, effect) {
		var that = this;
		var $array = new Array(this._total);
		var lastId;
		var count = 0;
		switch (effect) {
			case EFFECTS["block.left"]:
				lastId = this._$blocks.data("brId");
				for (var j = 0; j < this._numCols; j++) {
					for (var i = 0; i < this._numRows; i++) {
						$array[count++] = this._$blockArr[i][j];		
					}
				}
				break;
			case EFFECTS["block.right"]:
				lastId = this._$blocks.data("blId");
				for (var j = this._numCols - 1; j >= 0; j--) {
					for (var i = 0; i < this._numRows; i++) {
						$array[count++] = this._$blockArr[i][j];		
					}
				}					
				break;
			case EFFECTS["block.top"]:
				lastId = this._$blocks.data("brId");
				for (var i = 0; i < this._numRows; i++) {
					for (var j = 0; j < this._numCols; j++) {
						$array[count++] = this._$blockArr[i][j];		
					}
				}					
				break;
			default:
				lastId = this._$blocks.data("trId");
				for (var i = this._numRows - 1; i >= 0; i--) {
					for (var j = 0; j < this._numCols; j++) {
						$array[count++] = this._$blockArr[i][j];		
					}
				}
		}
		count = 0;
		this._intervalId = setInterval(
			function() {
				$array[count++].animate({width:that._size, height:that._size}, that._duration, that._rotator._easing,
						function() {
							if ($(this).attr("id") == lastId) {
								that._rotator.showContent($img);
							}
						});
				if (count == that._total) {
					clearInterval(that._intervalId);
				}
			}, this._delay);
	}
	
	//random block effect
	Blocks.prototype.randomAnimate = function($img) {
		var that = this;
		shuffleArray(this._$arr);
		var i = 0;
		count = 0;
		this._intervalId = setInterval(
			function() {
				that._$arr[i].animate({top:that._$arr[i].data("top"), width:that._size, height:that._size, opacity:1}, that._duration, that._rotator._easing,
						function() {
							if (++count == that._total) {
								that._rotator.showContent($img);
							}
						});
				i++;
				if (i == that._total) {
					clearInterval(that._intervalId);
				}
			}, this._delay);
	}
	
	//spiral effect
	Blocks.prototype.spiral = function($img, spiralOut) {
		var that = this;
		var i = 0, j = 0;
		var rowCount = this._numRows - 1;
		var colCount = this._numCols - 1;
		var dir = 0;
		var limit = colCount;
		var $array = new Array();
		while (rowCount >= 0 && colCount >=0) {
			var count = 0; 
			while(true) { 
				$array[$array.length] = this._$blockArr[i][j];
				if ((++count) > limit) {
					break;
				}
				switch(dir) {
					case 0:
						j++;
						break;
					case 1:
						i++;
						break;
					case 2:
						j--;
						break;
					case 3:
						i--;
				}
			} 
			switch(dir) {
				case 0:
					dir = 1;
					limit = (--rowCount);
					i++;
					break;
				case 1:
					dir = 2;
					limit = (--colCount);
					j--;
					break;
				case 2:
					dir = 3;
					limit = (--rowCount);
					i--;
					break;
				case 3:
					dir = 0;
					limit = (--colCount);
					j++;
			}
		}
		if ($array.length > 0) {
			if (spiralOut) {
				$array.reverse();
			}
			var end = $array.length - 1;
			var lastId = $array[end].attr("id");
			var k = 0;			
			this._intervalId = setInterval(
				function() {
					$array[k].animate({opacity:1}, that._duration, that._rotator._easing,
						function() {
							if ($(this).attr("id") == lastId) {
								that._rotator.showContent($img);
							}
						});					
					if (k == end) {
						clearInterval(that._intervalId);
					}	
					k++;
				}, this._delay);				
		}
	}
		
	//class Rotator
	function ListRotator($obj, opts) {
		this._screenWidth =  	getPosNumber(opts.screen_width, 600);
		this._screenHeight = 	getPosNumber(opts.screen_height, 300);
		this._itemWidth =		getPosNumber(opts.item_width, 250);
		this._itemHeight =		getPosNumber(opts.item_height, 75);
		this._numDisplay =		getPosNumber(opts.item_display, 4);
		this._rotate = 			opts.auto_start;	
		this._duration =   		getPosNumber(opts.transition_speed, DURATION);
		this._defaultEffect = 	opts.transition.toLowerCase();
		this._easing =			opts.easing;
		this._scrollType = 		window.Touch ? "swipe" : opts.scroll_type.toLowerCase();
		this._displayArrow =  	opts.display_arrow;
		this._displayThumbs =	opts.display_thumbs;
		this._displayScrollbar =  opts.display_scrollbar;
		this._displayPlayButton = opts.display_playbutton;
		this._displayNumber = 	opts.display_number;
		this._displayTimer =	opts.display_timer;
		this._textEffect = 		opts.text_effect.toLowerCase();
		this._textSync =		opts.text_sync;
		this._playOnce =		opts.play_once;
		this._listAlign = 		opts.list_align.toLowerCase();
		this._moveBy1 = 		opts.move_one;
		this._autoCenter =		opts.auto_center;
		this._textMouseover = 	window.Touch ? false : opts.text_mouseover;
		this._pauseMouseover = 	window.Touch ? false : opts.pause_mouseover;
		this._cpMouseover = 	window.Touch ? false : opts.cpanel_mouseover;
		this._mouseoverSelect =	opts.mouseover_select;
		this._timerAlign = 		opts.timer_align.toLowerCase();
		this._cpAlign = 		opts.cpanel_align.toUpperCase();
		this._defaultDelay = 	getPosNumber(opts.delay, DEFAULT_DELAY);
		this._shuffle = 		opts.shuffle;
		this._vSize = 			getPosNumber(opts.vert_size, STRIPE_SIZE);
		this._hSize = 			getPosNumber(opts.horz_size, STRIPE_SIZE);
		this._bSize = 			getPosNumber(opts.block_size, BLOCK_SIZE);
		this._vDelay = 			getPosNumber(opts.vstripe_delay, 90);
		this._hDelay = 			getPosNumber(opts.hstripe_delay, 180);
		this._bDelay = 			getPosNumber(opts.block_delay, 35);
		this._autoAdjust = 		opts.auto_adjust;
		
		this._numItems;
		this._currIndex;
		this._prevIndex;
		this._prevSlots;
		this._nextSlots;
		this._maxSlots;
		this._pos;
		this._delay;	
		this._vStripes;
		this._hStripes;
		this._blocks;	
		this._range;				
		this._dest;
		this._scrollSpeed;		
		this._scrollId;
		this._timerId;
		this._blockEffect;
		this._hStripeEffect;
		this._vStripeEffect;
		this._dir;
		this._slideCoord;
		this._listCoord;
		
		this._$rotator;
		this._$screen;
		this._$strip;
		this._$thumbPanel;
		this._$list;
		this._$listItems;
		this._$timer;
		this._$mainLink;			
		this._$textBox;
		this._$innerText;
		this._$preloader;
		this._$cpanel;
		this._$playButton;
		this._$numInfo;
		this._$arrow;		
		this._$containers;
		this._$upPane;
		this._$downPane;		
		this._$scrollbar;
		this._$knob;
		this._$obj = $obj;
		this.init();
	}		
	
	//init rotator
	ListRotator.prototype.init = function() {
		this._$rotator = this._$obj.find(".l-rotator");
		this._$screen = this._$rotator.find(".screen");
		this._$thumbPanel = this._$rotator.find(".thumbnails");
		this._$list = this._$thumbPanel.find(">ul:first");
		this._$listItems = this._$list.find(">li");
		this._blockEffect = this._hStripeEffect = this._vStripeEffect = false;
		this.checkEffect(EFFECTS[this._defaultEffect]);
		this._scrollId = null;
		this._timerId = null;
	
		this._currIndex = 0;
		this._prevIndex = -1;
		this._numItems = this._$listItems.size();				
		this._pos = 0;	
		this._maxSlots = this._numItems - this._numDisplay;
		this._prevSlots = 0;
		this._nextSlots = this._maxSlots;
		
		this.initMainScreen();			
		this.initItems();			
		this.initThumbPanel();
		
		if (this._textMouseover) {
			this._$rotator.bind("mouseenter", {elem:this}, this.displayText).bind("mouseleave", {elem:this}, this.hideText);
		}
		else {
			this._$rotator.bind(UPDATE_TEXT, {elem:this}, this.updateText);
		}
		
		this._$rotator.css({width:this._screenWidth + this._itemWidth, height:this._$thumbPanel.height() > this._screenHeight ? this._$thumbPanel.height() : this._screenHeight});
		if (this._pauseMouseover) {
			this._$rotator.bind("mouseenter", {elem:this}, this.stopRotate).bind("mouseleave", {elem:this}, this.startRotate);
		}
		
		if (this._vStripeEffect) {
			this._vStripes = new VertStripes(this);
		}
		if (this._hStripeEffect) {
			this._hStripes = new HorzStripes(this);
		}
		if (this._blockEffect) {
			this._blocks = new Blocks(this);
		}
		
		if (window.Touch) {
			this._slideCoord = {start:-1, end:-1};
			if (this._defaultEffect == "v.slide") {
				this._$screen.bind("touchstart", {elem:this}, this.touchVStart).bind("touchmove", {elem:this}, this.touchVMove);
			}
			else {
				this._$screen.bind("touchstart", {elem:this}, this.touchStart).bind("touchmove", {elem:this}, this.touchMove);
			}
			this._$screen.bind("touchend", {elem:this}, this.touchEnd);
		}
		else {
			try {
				this._$screen.bind("mousewheel", {elem:this}, this.mouseScrollContent).bind("DOMMouseScroll", {elem:this}, this.mouseScrollContent);
			}
			catch(ex) {}
		}
	
		this.loadImg(0);
		this.loadContent(this._currIndex);
	}
	
	//mousewheel scroll content
	ListRotator.prototype.mouseScrollContent = function(e) {
		var that = e.data.elem;
		if (!that._$strip.is(":animated")) {
			var delta = (typeof e.originalEvent.wheelDelta == "undefined") ?  -e.originalEvent.detail : e.originalEvent.wheelDelta;
			delta > 0 ? that.goPrev() : that.goNext();
		}
		return false;
	}
	
	//mousewheel scroll list
	ListRotator.prototype.mouseScrollThumbs = function(e) {
		var that = e.data.elem;
		if (!that._$list.is(":animated")) {
			var delta = (typeof e.originalEvent.wheelDelta == "undefined") ?  -e.originalEvent.detail : e.originalEvent.wheelDelta;
			delta > 0 ? that.scrollPrevThumbs(that._moveBy1) : that.scrollNextThumbs(that._moveBy1);
		}
	}
	
	ListRotator.prototype.touchStart = function(e) {
		e.data.elem._slideCoord.start = e.originalEvent.touches[0].pageX; 
	}
	
	ListRotator.prototype.touchMove = function(e) {
		e.preventDefault();
		e.data.elem._slideCoord.end = e.originalEvent.touches[0].pageX;
	}
	
	ListRotator.prototype.touchVStart = function(e) {
		e.data.elem._slideCoord.start = e.originalEvent.touches[0].pageY; 
	}
	
	ListRotator.prototype.touchVMove = function(e) {
		e.preventDefault();
		e.data.elem._slideCoord.end = e.originalEvent.touches[0].pageY;
	}
	
	ListRotator.prototype.touchEnd = function(e) {
		var that = e.data.elem;
		if (that._slideCoord.end >= 0) {
			if (Math.abs(that._slideCoord.start - that._slideCoord.end) > SWIPE_MIN) {
				if (that._slideCoord.end < that._slideCoord.start) {
					that.goNext();
				}
				else {
					that.goPrev();									
				}
			}
		}
		that._slideCoord.start = that._slideCoord.end = -1;
	}
	
	ListRotator.prototype.listTouchStart = function(e) {
		e.data.elem._listCoord.start = e.originalEvent.touches[0].pageY; 
	}
	
	ListRotator.prototype.listTouchMove = function(e) {
		e.preventDefault();
		e.data.elem._listCoord.end = e.originalEvent.touches[0].pageY;
	}
	
	ListRotator.prototype.listTouchEnd = function(e) {
		var that = e.data.elem;
		if (that._listCoord.end >= 0) {
			if (Math.abs(that._listCoord.start - that._listCoord.end) > SWIPE_MIN) {
				if (that._listCoord.end < that._listCoord.start) {
					that.scrollNextThumbs(this._moveBy1);
				}
				else {
					that.scrollPrevThumbs(this._moveBy1);									
				}
			}
		}
		that._listCoord.start = that._listCoord.end = -1;
	}
	
	//add to screen
	ListRotator.prototype.addToScreen = function(content) {
		this._$mainLink.append(content);
	}
	
	//init main screen
	ListRotator.prototype.initMainScreen = function() {
		var content =  "<div id='preloader'></div>\
						<div id='timer'></div>\
						<div class='textbox'>\
							<div class='inner-bg'></div>\
							<div class='inner-text'></div>\
						</div>\
						<div class='cpanel'>\
							<div id='play-btn'></div>\
							<div id='num-info'></div>\
						</div>";
		this._$screen.append(content).css({width:this._screenWidth, height:this._screenHeight});
		this._$preloader = this._$screen.find("#preloader");
		this.initTimerBar();
		this._$textBox 	= this._$screen.find(".textbox");
		this._$innerText = this._$textBox.find(".inner-text");
		
		this._$strip = $("<div class='strip'></div>");
		if (this._defaultEffect == "h.slide") {
			this._$screen.append(this._$strip);
			this._$strip.css({width:2*this._screenWidth, height:this._screenHeight});
			this._$listItems.removeAttr("effect");
		}
		else if (this._defaultEffect == "v.slide"){
			this._$screen.append(this._$strip);
			this._$strip.css({width:this._screenWidth, height:2*this._screenHeight});
			this._$listItems.removeAttr("effect");
		}
		else {
			this._$mainLink = $("<a href='#'></a>");
			this._$screen.append(this._$mainLink);
		}
		this.initCPanel();			
	}

	//init timer bar
	ListRotator.prototype.initTimerBar = function() {
		this._$timer = this._$screen.find("#timer").data("pct", 1);
		if (this._displayTimer) {
			this._$timer.css("top", this._timerAlign == "top" ? 0 : this._screenHeight - this._$timer.height()).css("visibility", "visible");
		}
		else {
			this._$timer.hide();
		}
	}
	
	//init cpanel
	ListRotator.prototype.initCPanel = function() {
		this._$cpanel = this._$screen.find(".cpanel");
		if (!this._displayNumber && !this._displayPlayButton) {
			this._$cpanel.remove();
			return;
		}
		
		this._$numInfo = this._$cpanel.find("#num-info");
		if (this._displayNumber) {
			var digits = getNumDigits(this._numItems);
			var str = "";
			for (var i = 0; i < digits; i++) {
				str += "0";
			}
			str += " / " + str
			this._$numInfo.html(str).width(this._$numInfo.width()).html("");
			var that = this;				
			this._$rotator.bind(UPDATE_NUMBER, function() { 
				that._$numInfo.html((that._currIndex + 1) + " / " + that._numItems); 
			});
		}
		else {
			this._$numInfo.remove();
		}
		
		this._$playButton = this._$cpanel.find("#play-btn");
		if (this._displayPlayButton) {
			this._$playButton.bind("click", {elem:this}, this.togglePlay).toggleClass("pause", this._rotate);
		}
		else {
			this._$playButton.remove();
		}
		
		switch(ALIGN[this._cpAlign]) {
			case ALIGN["TL"]:
				this._$cpanel.css({top:0, left:0});				
				break;
			case ALIGN["TC"]:
				this._$cpanel.css({top:0, left:Math.floor((this._screenWidth - this._$cpanel.outerWidth(true))/2)});
				break;
			case ALIGN["TR"]:
				this._$cpanel.css({top:0, left:this._screenWidth - this._$cpanel.outerWidth(true)});
				break;
			case ALIGN["BL"]:
				this._$cpanel.css({top:this._screenHeight - this._$cpanel.outerHeight(true), left:0});
				break;
			case ALIGN["BC"]:
				this._$cpanel.css({top:this._screenHeight - this._$cpanel.outerHeight(true), left:Math.floor((this._screenWidth - this._$cpanel.outerWidth(true))/2)});
				break;
			default:
				this._$cpanel.css({top:this._screenHeight - this._$cpanel.outerHeight(true), left:this._screenWidth - this._$cpanel.outerWidth(true)});				
		}
		
		if (this._cpMouseover) {
			this._$cpanel.css("display","none");
			this._$rotator.bind("mouseenter", {elem:this}, this.showCPanel).bind("mouseleave", {elem:this}, this.hideCPanel);
		}
		this._$cpanel.css("visibility", "visible");
	}
	
	ListRotator.prototype.showCPanel = function(e) {
		e.data.elem._$cpanel.stop(true,true).fadeIn(DEFAULT_SPEED);
	}
	
	ListRotator.prototype.hideCPanel = function(e) {
		e.data.elem._$cpanel.stop(true,true).fadeOut(DEFAULT_SPEED);
	}
	
	//init items
	ListRotator.prototype.initItems = function() {
		var padding = this._$innerText.outerHeight() - this._$innerText.height();
		var itemSize = this._$listItems.size();
		for (var i = 0; i < itemSize; i++) {
			var $item = this._$listItems.eq(i);
			var $imgLink = $item.find(">a:first");
			var itemEffect = EFFECTS[$item.attr("effect")];
			if ((typeof itemEffect == "undefined") || itemEffect ==  EFFECTS["h.slide"] || itemEffect ==  EFFECTS["v.slide"]) {
				itemEffect = EFFECTS[this._defaultEffect];
			}
			else {
				this.checkEffect(itemEffect);						
			}
			$item.data({imgurl:$imgLink.attr("href"), delay:getPosNumber($item.attr("delay"), this._defaultDelay), effect:itemEffect});
			this.initTextData($item, padding);	
		}
		this._$innerText.html("").css({width:"auto", height:"auto"});
		this._$textBox.css("visibility", "visible");
				
		if (this._shuffle) {
			this.shuffleItems();
		}
		
		this._$containers = this._$listItems.find(">div.thumb");
		this._$containers.css(this._listAlign == LEFT ? {"float":"left", "border-right-width":1} : {"float":"right", "border-left-width":1});
		this._$containers.css({width:this._itemWidth - (this._$containers.outerWidth() - this._$containers.width()), height:this._itemHeight - (this._$containers.outerHeight() - this._$containers.height())})
				   .mousedown(preventDefault);
		
		if (!this._displayThumbs) {
			this._$containers.find(">img:first").hide();
		}		
	}	
	
	//init thumb panel
	ListRotator.prototype.initThumbPanel = function() {
		var arrowWidth = 0;
		if (this._displayArrow) {
			this._$arrow = $("<div>&nbsp;&nbsp;&nbsp;</div>").attr("id", this._listAlign == RIGHT ? "left-arrow" : "right-arrow").height(this._itemHeight);			
			this._$listItems.eq(0).append(this._$arrow);
			arrowWidth = this._$arrow.width();
		}
		else {
			this._$listItems.addClass("square");
		}
		this._$listItems.css({width:this._itemWidth + arrowWidth, height:this._itemHeight});
		this._$list.height(this._numItems * this._$listItems.outerHeight());
		this._$thumbPanel.css({width:this._$listItems.width(), height:this._numDisplay * this._$listItems.outerHeight()});
		this._$thumbPanel.bind(this._mouseoverSelect ? "mouseover" : "click", {elem:this}, this.selectItem);						
		
		this._range = this._$list.height() - this._$thumbPanel.height();
		
		if (this._listAlign == LEFT) {
			this._$thumbPanel.css("left", 0);
			this._$screen.css("left", this._itemWidth);
		}
		else {
			this._$screen.css("left", 0);
			this._$thumbPanel.css("left", this._screenWidth - arrowWidth);				
		}
		
		switch(this._scrollType) {
			case "mouse_click":
				this.initDButtons();
				this._$upPane.bind("click", {elem:this}, this.prevThumbs).find("#up-btn").css("cursor","pointer");
				this._$downPane.bind("click", {elem:this}, this.nextThumbs).find("#down-btn").css("cursor","pointer");
				try { 
					this._$thumbPanel.bind("mousewheel", {elem:this}, this.mouseScrollThumbs).bind("DOMMouseScroll", {elem:this}, this.mouseScrollThumbs);
				} catch(ex) {}
				break;
			case "mouse_over":
				this.initDButtons();
				this._$upPane.bind("mouseenter", {elem:this}, this.scrollUp).bind("mouseleave", {elem:this}, this.stopThumbList);
				this._$downPane.bind("mouseenter", {elem:this}, this.scrollDown).bind("mouseleave", {elem:this}, this.stopThumbList);
				break;
			case "mouse_move":
				this._$thumbPanel.bind("mousemove", {elem:this}, this.mousemoveScroll);
				break;
			case "swipe":
				this._listCoord = {start:-1, end:-1};
				this._$thumbPanel.bind("touchstart", {elem:this}, this.listTouchStart).bind("touchmove", {elem:this}, this.listTouchMove).bind("touchend", {elem:this}, this.listTouchEnd);
				break;
			default:
				this._$listItems.bind("click", {elem:this}, this.itemClick);
				try { 
					this._$thumbPanel.bind("mousewheel", {elem:this}, this.mouseScrollThumbs).bind("DOMMouseScroll", {elem:this}, this.mouseScrollThumbs);
				} catch(ex) {}
		}
		
		if (this._displayScrollbar && this._range > 0) {
			this.initScrollbar();
		}
		
		if (this._autoAdjust) {
			this._$rotator.bind(AUTO_ADJUST, {elem:this}, this.adjustThumbs);
			if (!window.Touch) {
				var that = this;
				this._$thumbPanel.hover(function() { that._$rotator.unbind(AUTO_ADJUST); }, function() { that._$rotator.bind(AUTO_ADJUST, {elem:that}, that.adjustThumbs); });
			}
		}
	}
	
	ListRotator.prototype.initScrollbar = function() {
		var that = this;
		this._$thumbPanel.append("<div id='scrollbar'><div id='knob'></div></div>");
		this._$scrollbar = this._$thumbPanel.find("#scrollbar");
		this._$knob = 	 this._$scrollbar.find("#knob");
		this._$scrollbar.css("left", this._listAlign == LEFT ? 0 : this._$thumbPanel.width() - this._$scrollbar.width());								
		this._$knob.height(Math.floor((this._numDisplay/this._numItems) * this._$scrollbar.height()));
		
		var scrollRange = this._$scrollbar.height() - this._$knob.height();
		var scrollRatio = scrollRange/this._range;
		this._$scrollbar.data({range:scrollRange, ratio:scrollRatio});
		
		this._$rotator.bind(SHOW_SCROLLBAR, function() { that._$scrollbar.stop(true,true).fadeIn(DEFAULT_SPEED); })
				.bind(HIDE_SCROLLBAR, function() { that._$scrollbar.stop(true,true).fadeOut(DEFAULT_SPEED); })
				.bind(MOVE_KNOB, function() { that._$knob.stop(true).animate({top:Math.round(-that._pos * scrollRatio)}, that._scrollSpeed); });
		this._$scrollbar.hide().css("visibility","visible");
	}
	
	//init directional buttons
	ListRotator.prototype.initDButtons = function() {
		this._$thumbPanel.append("<div class='btn-pane'><div id='up-btn'></div></div>\
							<div class='btn-pane'><div id='down-btn'></div></div>");
		var $dPane = this._$thumbPanel.find(".btn-pane");
		$dPane.css({opacity:0, width:this._itemWidth});
		$dPane.hover(this.showDPane, this.hideDPane);
			  
		if (this._displayArrow && this._listAlign == RIGHT) {
			$dPane.css("left", this._$arrow.width());
		}
		this._$upPane =   $dPane.has("#up-btn");
		this._$downPane = $dPane.has("#down-btn");
		this._$downPane.css("top", this._$thumbPanel.height() - this._$downPane.height());
		$dPane.css("visibility", "visible");
		this._$rotator.bind(UPDATE_BUTTONS, {elem:this}, this.updateButtons).trigger(UPDATE_BUTTONS);
	}
	
	ListRotator.prototype.showDPane = function() {
		$(this).stop(true, true).animate({opacity:1}, DEFAULT_SPEED);
	}
	
	ListRotator.prototype.hideDPane = function() {
		$(this).stop(true, true).animate({opacity:0}, DEFAULT_SPEED);
	}
	
	//update control
	ListRotator.prototype.updateButtons = function(e) {
		var that = e.data.elem;
		that._pos < 0 ? that._$upPane.stop(true,true).fadeIn(DEFAULT_SPEED): that._$upPane.stop(true,true).fadeOut(DEFAULT_SPEED);
		that._pos > -that._range ? that._$downPane.stop(true,true).fadeIn(DEFAULT_SPEED) : that._$downPane.stop(true,true).fadeOut(DEFAULT_SPEED);
	}
	
	//move to previous thumbs
	ListRotator.prototype.prevThumbs = function(e) {
		var that = e.data.elem;
		that.scrollPrevThumbs(that._moveBy1);
	}
	
	//move to next thumbs
	ListRotator.prototype.nextThumbs = function(e) {
		var that = e.data.elem;
		that.scrollNextThumbs(that._moveBy1);
	}
	
	//move to previous thumbs
	ListRotator.prototype.scrollPrevThumbs = function(move1) {
		if (this._nextSlots < this._maxSlots) {
			var slots = move1 ? 1 : Math.min(this._maxSlots - this._nextSlots, this._numDisplay);
			this._nextSlots += slots;
			this._prevSlots -= slots;
			this.moveList();
		}				
		return false;
	}
	
	//move to next thumbs
	ListRotator.prototype.scrollNextThumbs = function(move1) {
		if (this._prevSlots < this._maxSlots) {
			var slots = move1 ? 1 : Math.min(this._maxSlots - this._prevSlots, this._numDisplay);
			this._prevSlots += slots;
			this._nextSlots -= slots;
			this.moveList();
		}				
		return false;
	}
	
	//item click move
	ListRotator.prototype.itemClick = function(e) {
		var that = e.data.elem;
		var index = ($(this).index() - that._prevSlots)%that._numDisplay;
		if (index+1 == that._numDisplay) {
			that.scrollNextThumbs(true);
		}
		else if (index == 0) {
			that.scrollPrevThumbs(true);
		}
	}
	
	//mouseover scroll up
	ListRotator.prototype.scrollUp = function(e) {
		var that = e.data.elem;
		that._$downPane.stop(true,true).fadeIn(DEFAULT_SPEED);
		that._$rotator.trigger(SHOW_SCROLLBAR);
		
		that._scrollSpeed = -that._$list.stop(true).position().top * SCROLL_RATE;
		that._$list.animate({top:0}, that._scrollSpeed, 
									function() { 
										that._$upPane.stop(true,true).fadeOut(DEFAULT_SPEED); 
										that._$rotator.trigger(HIDE_SCROLLBAR);
									});
		that._$knob.stop(true).animate({top:0}, that._scrollSpeed);
	}
	
	//mouseover scroll down
	ListRotator.prototype.scrollDown = function(e) {
		var that = e.data.elem;
		that._$upPane.stop(true,true).fadeIn(DEFAULT_SPEED);
		that._$rotator.trigger(SHOW_SCROLLBAR);
		
		that._scrollSpeed = (that._range + that._$list.stop(true).position().top) * SCROLL_RATE;
		that._$list.animate({top:-that._range}, that._scrollSpeed, 
									function() { 
										that._$downPane.stop(true,true).fadeOut(DEFAULT_SPEED);
										that._$rotator.trigger(HIDE_SCROLLBAR);
									});
		that._$knob.stop(true).animate({top:that._$scrollbar.data("range")}, that._scrollSpeed);
	}
	
	//stop list
	ListRotator.prototype.stopThumbList = function(e) {
		var that = (typeof e != "undefined") ? e.data.elem : this;
		that._$list.stop(true);					
		try { 
			that._$knob.stop(true);
		}
		catch (ex) {
			//no knob
		};
		that._$rotator.trigger(HIDE_SCROLLBAR);
	}
	
	//mouse move scroll
	ListRotator.prototype.mousemoveScroll = function(e) {
		var that = e.data.elem;
		var pct = Math.round(((e.pageY - that._$thumbPanel.offset().top)/that._$thumbPanel.height()) * 100)/100;
		that._dest = -Math.round(that._range * pct);
		if (that._scrollId == null && that._dest != that._$list.position().top) {
			that.stopThumbList();
			that._$rotator.trigger(SHOW_SCROLLBAR);
			that._scrollId = setInterval(function() {
				var yPos = that._$list.stop(true).position().top;
				if (yPos == that._dest) {
					clearInterval(that._scrollId);
					that._scrollId = null;
					that._$rotator.trigger(HIDE_SCROLLBAR);
				} 
				else {					
					var move = (that._dest - yPos) * SCROLL_DELAY;
					that._pos += move < 0 ? Math.min(-1, Math.round(move)) : Math.max(1, Math.round(move));
					that._$list.css("top", that._pos);
					try {
						that._$knob.css("top", Math.round(-that._pos * that._$scrollbar.data("ratio")));
					}
					catch (ex) {
						//no knob
					};
				}
			}, 30);
		}
	}
	
	//adjust thumbs
	ListRotator.prototype.adjustThumbs = function(e) {
		var that = e.data.elem;
		if (that._scrollId == null) {
			var slots = Math.min(that._currIndex, that._maxSlots);
			that._prevSlots = slots;
			that._nextSlots = that._maxSlots - that._prevSlots;			
			that.moveList();
		}
	}
	
	//move list
	ListRotator.prototype.moveList = function() {
		var that = this;
		this._pos = -this._prevSlots * this._$listItems.outerHeight();
		this._scrollSpeed = Math.min(MAX_SCROLL_SPEED, Math.abs(this._$list.position().top - this._pos) * SCROLL_RATE);
		if (this._scrollSpeed > 0) {
			this._$rotator.trigger(SHOW_SCROLLBAR);
		}
		this._$list.stop(true).animate({top:this._pos}, this._scrollSpeed, 
									function() { 
										that._$rotator.trigger(UPDATE_BUTTONS).trigger(HIDE_SCROLLBAR);
									});
		this._$rotator.trigger(MOVE_KNOB);
	}

	//init text data
	ListRotator.prototype.initTextData = function($item, padding) {				
		var $p = $item.find(">div:hidden");			
		var textWidth =  getPosNumber(parseInt($p.css("width")) - padding, 300);			
		var textHeight = getPosNumber(parseInt($p.css("height")) - padding, 0);
		this._$innerText.width(textWidth).html($p.html());
		if (textHeight < this._$innerText.height()) {
			textHeight = this._$innerText.height();
		}
		$item.data("textbox", {x:$p.css("left"), y:$p.css("top"), w:textWidth + padding, h:textHeight + padding + 1, color:$p.css("color"), bgcolor:$p.css("background-color")});
	} 
	
	//select list item
	ListRotator.prototype.selectItem = function(e) {
		var that = e.data.elem;
		var $item = $(e.target);
		if ($item[0].nodeName != "LI") {
			$item = $item.parents("li").eq(0);
		}
		var i = $item.index();
		if (i >= 0 && i != that._currIndex) {
			that._dir = i < that._currIndex ? PREV : NEXT; 
			that.resetTimer();
			that._prevIndex = that._currIndex;
			that._currIndex = i;
			that.loadContent(that._currIndex);
		}
		return false;
	}
	
	ListRotator.prototype.goPrev = function() {
		this.resetTimer();
		this._dir = PREV; 
		this._prevIndex = this._currIndex;
		if (this._currIndex > 0) {										
			this._currIndex--;
		}
		else {
			this._currIndex = this._numItems - 1;
		}
		this.loadContent(this._currIndex);				
	}
	
	ListRotator.prototype.goNext = function() {
		this.resetTimer();
		this._dir = NEXT; 
		this._prevIndex = this._currIndex;	
		if (this._currIndex < this._numItems - 1) {															
			this._currIndex++;					
		}
		else {
			this._currIndex = 0;
		}
		this.loadContent(this._currIndex);
	}	

	//play/pause
	ListRotator.prototype.togglePlay = function(e) {
		var that = e.data.elem;
		that._rotate = !that._rotate;
		$(this).toggleClass("pause", that._rotate);				
		that._rotate ? that.startTimer() : that.pauseTimer();
		return false;
	}
	
	//pause on last item
	ListRotator.prototype.pauseLast = function(i) {
		if (i == this._numItems - 1) {
			this._rotate = false;
			this._$playButton.toggleClass("pause", this._rotate);
		}
	}
	
	//start rotate
	ListRotator.prototype.startRotate = function(e) {
		var that = e.data.elem;
		that._rotate = true;
		that._$playButton.toggleClass("pause", that._rotate);
		that.startTimer();
	}

	//stop rotate
	ListRotator.prototype.stopRotate = function(e) {
		var that = e.data.elem;
		that._rotate = false;
		that._$playButton.toggleClass("pause", that._rotate);
		that.pauseTimer();
	}
	
	//update text box
	ListRotator.prototype.updateText = function(e) {
		var that = e.data.elem;
		if (!that._$textBox.data("visible")) {
			that._$textBox.data("visible", true);
			var text = that._$listItems.eq(that._currIndex).find(">div:hidden").html();
			if (text && text.length > 0) {			
				var data = that._$listItems.eq(that._currIndex).data("textbox");
				that._$innerText.css("color",data.color);
				that._$textBox.find(".inner-bg").css({"background-color":data.bgcolor, height:data.h-1});
				switch(TEXT_EFFECTS[that._textEffect]) {
					case TEXT_EFFECTS["fade"]:
						that.fadeInText(text, data);
						break;
					case TEXT_EFFECTS["down"]:
						that.expandText(text, data, {width:data.w, height:0}, {height:data.h});
						break;
					case TEXT_EFFECTS["right"]:
						that.expandText(text, data, {width:0, height:data.h}, {width:data.w});
						break;
					case TEXT_EFFECTS["left"]:
						that.expandText(text, data, {"margin-left":data.w, width:0, height:data.h}, {width:data.w, "margin-left":0});
						break;
					case TEXT_EFFECTS["up"]:
						that.expandText(text, data, {"margin-top":data.h, height:0, width:data.w}, {height:data.h, "margin-top":0});
						break;	
					default:
						that.showText(text, data);
				}
			}					
		}
	}
	
	//reset text box
	ListRotator.prototype.resetText = function() {
		this._$textBox.data("visible", false).stop(true, true);
		switch(TEXT_EFFECTS[this._textEffect]) {					
			case TEXT_EFFECTS["fade"]:
			case TEXT_EFFECTS["down"]:
			case TEXT_EFFECTS["left"]:					
			case TEXT_EFFECTS["up"]:					
			case TEXT_EFFECTS["right"]:					
				if (jQuery.browser.msie) {
					this._$innerText.css("opacity",0);
				}
				this._$textBox.fadeOut(TEXT_SPEED, function() { $(this).css("display", "none"); });
				break;
			default:
				this._$textBox.css("display", "none");
		}
	}
	
	//fade in text effect
	ListRotator.prototype.fadeInText = function(text, data) {
		var that = this;
		this._$innerText.css("opacity",1).html(text);
		this._$textBox.css({top:data.y, left:data.x, width:data.w, height:data.h})
				.stop(true, true).fadeIn(TEXT_SPEED, function() {
															if (jQuery.browser.msie) {
																that._$innerText[0].style.removeAttribute('filter'); 
															}
														});  
	}
	
	//expand text effect
	ListRotator.prototype.expandText = function(text, data, props1, props2) {
		var that = this;
		this._$innerText.css("opacity",1).html("");
		this._$textBox.stop(true, true).css({display:"block", top:data.y, left:data.x, "margin-top":0, "margin-left":0}).css(props1).animate(props2, TEXT_SPEED,  
			function () {  
				that._$innerText.html(text);
			});  
	}
	
	//show text effect
	ListRotator.prototype.showText = function(text, data) {
		this._$textBox.stop(true).css({display:"block", top:data.y, left:data.x, width:data.w, height:data.h});  
		this._$innerText.html(text);
	}
	
	//display text panel on mouseover
	ListRotator.prototype.displayText = function(e) {
		var that = e.data.elem;
		that._$rotator.unbind(UPDATE_TEXT).bind(UPDATE_TEXT, {elem:that}, that.updateText).trigger(UPDATE_TEXT);
	}

	//hide text panel on mouseovers
	ListRotator.prototype.hideText = function(e) {
		var that = e.data.elem;
		that._$rotator.unbind(UPDATE_TEXT);
		that.resetText();
	}
	
	//load current content
	ListRotator.prototype.loadContent = function(i) {
		this._$rotator.trigger(AUTO_ADJUST).trigger(UPDATE_NUMBER);
		
		if (this._playOnce) {
			this.pauseLast(i);
		}
		
		//select thumb
		var $selectedItem = this._$listItems.eq(i);
		this._$listItems.filter(".selected").removeClass("selected");
		$selectedItem.removeClass("item-over").addClass("selected").append(this._$arrow);
		
		//set delay
		this._delay =	$selectedItem.data("delay");
		
		//reset text
		this.resetText();
		if (!this._textSync) {
			this._$rotator.trigger(UPDATE_TEXT);
		}
		
		//set link
		if (this._$mainLink) {
			var $currLink = $selectedItem.find(">a").eq(1);
			var href = $currLink.attr("href");
			if (href) {
				this._$mainLink.unbind("click", preventDefault).css("cursor","pointer").attr({href:href, target:$currLink.attr("target")});
			}
			else {
				this._$mainLink.click(preventDefault).css("cursor","default");
			}
		}
		
		//load image
		if ($selectedItem.data("img")) {
			this._$preloader.hide();
			this.displayContent($selectedItem.data("img"));
		}	
		else {	
			//load new image
			var that = this;
			var $img = $("<img class='main-img'/>");					
			$img.load(
				function() {
					that._$preloader.hide();
					that.storeImg($selectedItem, $(this));
					that.displayContent($(this));
				}
			).error(
				function() {
					alert("Error loading image");
				}
			);
			this._$preloader.show();
			$img.attr("src", $selectedItem.data("imgurl"));
		}	    
	}
	
	//display content
	ListRotator.prototype.displayContent = function($img) {
		if (this._vStripeEffect) {
			this._vStripes.clear();
		}
		if (this._hStripeEffect) {
			this._hStripes.clear();
		}
		if (this._blockEffect) {
			this._blocks.clear();
		}
		if (this._vStripeEffect || this._hStripeEffect || this._blockEffect) {
			this.setPrevious();
		}
		
		var effect = this._$listItems.eq(this._currIndex).data("effect");		
		if (effect == EFFECTS["none"] || (typeof effect == "undefined")) {
			this.showContent($img);
			return;
		}
		else if (effect == EFFECTS["fade"]) {
			this.fadeInContent($img);
			return;
		}
		else if (effect == EFFECTS["h.slide"]) {
			this.slideContent($img, "left", this._screenWidth);
			return;
		}
		else if (effect == EFFECTS["v.slide"]) {
			this.slideContent($img, "top", this._screenHeight);
			return;
		}
		
		if (effect == EFFECTS["random"]) {
			effect = Math.floor(Math.random() * (ei - 5));
		}
		
		if (effect <= EFFECTS["spiral.out"]) {
			this._blocks.displayContent($img, effect);
		}
		else if (effect <= EFFECTS["blinds.right"]) {
			this._vStripes.displayContent($img, effect);
		}
		else {
			this._hStripes.displayContent($img, effect);				
		}
	}
	
	//set previous
	ListRotator.prototype.setPrevious = function() {
		if (this._prevIndex >= 0) {
			var currSrc = this._$mainLink.find("img#curr-img").attr("src");
			var prevSrc = this._$listItems.eq(this._prevIndex).data("imgurl");
			if (currSrc != prevSrc) {
				this._$mainLink.find("img.main-img").attr("id","").hide();
				var $img = this._$mainLink.find("img.main-img").filter(function() { return $(this).attr("src") == prevSrc; });
				$img.eq(0).show();
			}
		}
	}
	
	//display content (no effect)
	ListRotator.prototype.showContent = function($img) {
		if (this._textSync) {
			this._$rotator.trigger(UPDATE_TEXT);
		}
		this._$mainLink.find("img.main-img").attr("id","").hide();
		$img.attr("id", "curr-img").show();
		this.startTimer();
	}
	
	//display content (fade effect)
	ListRotator.prototype.fadeInContent = function($img) {
		var that = this;
		this._$mainLink.find("img#curr-img").stop(true, true);
		this._$mainLink.find("img.main-img").attr("id","").css("z-index", 0);
		$img.attr("id", "curr-img").stop(true, true).css({opacity:0,"z-index":1}).show().animate({opacity:1}, this._duration, this._easing,
			function() {
				that._$mainLink.find("img.main-img:not('#curr-img')").hide();
				if (that._textSync) {
					that._$rotator.trigger(UPDATE_TEXT);
				}
				that.startTimer();
			}
		);
	}
	
	//slide content
	ListRotator.prototype.slideContent = function($currImg, position, moveby) {
		this._$strip.stop(true,true);
		var $prevImg = $("#curr-img", this._$strip);
		if ($prevImg.size() > 0) {
			var that = this;		
			this._$strip.find(".main-img").attr("id","").parents(".content-box").css({top:0,left:0});
			$currImg.attr("id", "curr-img").parents(".content-box").show();
			var $img, destination;
			if (this._dir == PREV) {
				this._$strip.css(position, -moveby);
				$img = $prevImg;				
				destination = 0;
			}
			else {
				$img = $currImg;
				destination = -moveby;
			}
			$img.parents(".content-box").css(position,moveby);
			var prop = (position == "top") ? {top:destination} : {left:destination};
			this._$strip.stop(true,true).animate(prop, this._duration, this._easing,
								function() {
									that._$strip.find(".main-img:not('#curr-img')").parents(".content-box").hide();
									$img.parents(".content-box").css({top:0,left:0});
									that._$strip.css({top:0,left:0});
									if (that._textSync) {
										that._$rotator.trigger(UPDATE_TEXT);
									}
									that.startTimer();
								});
		}
		else {
			this._$strip.css({top:0,left:0});
			this._$strip.find(".main-img").parents(".content-box").hide().css({top:0,left:0});
			$currImg.attr("id", "curr-img").parents(".content-box").show();
			if (this._textSync) {
				this._$rotator.trigger(UPDATE_TEXT);
			}
			this.startTimer();
		}
	}
	
	//load image
	ListRotator.prototype.loadImg = function(loadIndex) {
		try {
			var that = this;
			var $item = this._$listItems.eq(loadIndex);
			var $img = $("<img class='main-img'/>");					
			$img.load(function() {
						if (!$item.data("img")) {
							that.storeImg($item, $(this));
						}
						loadIndex++
						if (loadIndex < that._numItems) {
							that.loadImg(loadIndex);
						}
					})
				.error(function() {
						loadIndex++
						if (loadIndex < that._numItems) {
							that.loadImg(loadIndex);
						}
					});
			$img.attr("src", $item.data("imgurl"));
		}
		catch(ex) {}
	}
	
	//process & store image
	ListRotator.prototype.storeImg = function($item, $img) {
		if (this._defaultEffect == "h.slide" || this._defaultEffect == "v.slide") {
			this._$strip.append($img);
			this.centerImg($img);
			var $div = $("<div class='content-box'></div>").css({width:this._screenWidth, height:this._screenHeight});
			$img.wrap($div);
			$img.css("display","block");
			var $link = $item.find(">a").eq(1);
			if ($link) {
			    $img.wrap($link);
			}
		}
		else {
			this._$mainLink.append($img);
			this.centerImg($img);
		}
		$item.data("img", $img);
	}
	
	//center image
	ListRotator.prototype.centerImg = function($img) {
		if (this._autoCenter && $img.width() > 0 && $img.height() > 0) {
			var tDiff = (this._screenHeight - $img.height())/2;
			var lDiff = (this._screenWidth  - $img.width())/2
			$img.css({top:tDiff, left:lDiff});
		}
	}
	
	//start timer
	ListRotator.prototype.startTimer = function() {
		if (this._rotate && this._timerId == null) {
			var that = this;
			var delay = Math.round(this._$timer.data("pct") * this._delay);
			this._$timer.animate({width:this._screenWidth+1}, delay, "linear");
			this._timerId = setTimeout(function() {
				that.resetTimer();
				that._dir = NEXT;
				that._prevIndex = that._currIndex;
				that._currIndex = that._currIndex < that._numItems - 1 ? that._currIndex + 1 : 0;
				that.loadContent(that._currIndex);
			}, delay);				
		}
	}
	
	//reset timer
	ListRotator.prototype.resetTimer = function() {
		clearTimeout(this._timerId);
		this._timerId = null;
		this._$timer.stop(true).width(0).data("pct", 1);
	}
	
	//pause timer
	ListRotator.prototype.pauseTimer = function() {
		clearTimeout(this._timerId);
		this._timerId = null;
		this._$timer.stop(true).data("pct", 1 - (this._$timer.width()/(this._screenWidth+1)));
	}
	
	//shuffle items
	ListRotator.prototype.shuffleItems = function() {
		var $items = new Array(this._numItems);
		for (var i = 0; i < this._numItems; i++) {
			var ri = Math.floor(Math.random() * this._numItems);
			var temp = this._$listItems.eq(i);
			$items[i] = this._$listItems.eq(ri);
			$items[ri] = temp;			
		}
		
		for (var i = 0; i < this._numItems; i++) {
			this._$list.append($items[i]);
		}
		
		this._$listItems = this._$list.find(">li");
	}
	
	ListRotator.prototype.checkEffect = function(num) {
		if (num == EFFECTS["random"]) {
			this._blockEffect = this._hStripeEffect = this._vStripeEffect = true;
		}
		else if (num <= EFFECTS["spiral.out"]) {
			this._blockEffect = true;
		}
		else if (num <= EFFECTS["blinds.right"]) {
			this._vStripeEffect = true;
		}
		else if (num <= EFFECTS["blinds.bottom"]) {
			this._hStripeEffect = true;
		}
	}
	
	//prevent default behavior
	function preventDefault() {
		return false;
	}
		
	//get positive number
	function getPosNumber(val, defaultVal) {
		if (!isNaN(val) && val > 0) {
			return val;
		}
		return defaultVal;
	}
	
	//shuffle array
	function shuffleArray(arr) {
		var total =  arr.length;
		for (var i = 0; i < total; i++) {
			var ri = Math.floor(Math.random() * total);
			var temp = arr[i];
			arr[i] = arr[ri];
			arr[ri] = temp;
		}	
	}
	
	//get number of digits
	function getNumDigits(num) {
		var count = 1;
		num = Math.abs(num);
		num = parseInt(num/10);
		while(num > 0) {
			count++;
			num = parseInt(num/10);
		}
		return count;
	}
		
	$.fn.wtListRotator = function(params) {
		var defaults = {
			screen_width:600,
			screen_height:300,
			item_width:250,
			item_height:75,
			item_display:4,
			list_align:LEFT,
			scroll_type:"mouse_move",			
			auto_start:true,
			delay:DEFAULT_DELAY,
			transition:"fade",
			transition_speed:DURATION,
			easing:"",
			auto_center:true,
			display_playbutton:true,
			display_number:true,
			display_timer:true,
			display_arrow:true,
			display_thumbs:true,
			display_scrollbar:true,
			mouseover_select:false,
			pause_mouseover:false,
			cpanel_mouseover:true,					
			text_mouseover:false,
			text_effect:"down",
			text_sync:true,			
			cpanel_align:"TR",
			timer_align:"bottom",
			move_one:false,
			auto_adjust:true,
			shuffle:false,
			play_once:false,			
			block_size:BLOCK_SIZE,
			vert_size:STRIPE_SIZE,
			horz_size:STRIPE_SIZE,
			block_delay:35,
			vstripe_delay:90,
			hstripe_delay:180	
		};
		
		var opts = $.extend({}, defaults, params);	
		return this.each(
			function() {
				var rotator = new ListRotator($(this), opts);
			}
		);
	}
})(jQuery);