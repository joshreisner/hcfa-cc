$(function(){
	$('table.draggable').tableDnD({
		dragHandle: '.reorder',
	    onDragClass: 'dragging',
	    onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;
            var order = [];
            for (var i = 0; i < rows.length; i++) {
				if (rows[i].id) order[order.length] = rows[i].id;
            }
	        $.post(location.href, { order: order }, function(data) {
	            if ($(table).hasClass('links')) {
			        $('ul.links').html(data);
				} else if ($(table).hasClass('spotlight')) {
			        $('div#spotlight').html(data);
				}
	        });
	    }
	});
});

//init tinymce
tinyMCE.init({
	mode : "textareas",
	extended_valid_elements : "a[href|target],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[align|style],p[style|align]",
	content_css : '/_hcfa-cc/style-textarea.css?' + new Date().getTime(),
	editor_selector : "mceEditor",
	editor_deselector : "mceNoEditor",
	statusbar: false,
	menubar: 'edit insert view format table tools',
	browser_spellcheck : true,
	plugins: [
		"advlist autolink lists link image charmap print preview anchor",
		"searchreplace visualblocks code fullscreen",
		"insertdatetime media table paste jbimages"
	],
	toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages",
	//toolbar: "insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages",
	relative_urls: false
});

//delete buttons
function promptRedirect(url, question) {
	if (question) {
		if (confirm(question)) location.href=url;
	} else {
		location.href=url;
	}
}

//bulletin board
$('table#bb').on('click', 'tr.thread', function(){
	location.href = $(this).find('a').first().attr('href');
});

if ($('table#bb').size()) {
	function update(count) {
		if (!count) count = $('table#bb tbody tr.thread').size();
		if (timerID) window.clearTimeout(timerID);
		$('table#bb tfoot input#more').attr('disabled', true);
		$.ajax({
			type: 'GET',
			url: '/bb/ajax.php',
			timeout: 2000,
			data: { count: count },
			success: function(data) {
				//console.log('success');
				$('table#bb tbody').html(data);
				timerID = window.setTimeout(update, 10000);
				$('table#bb tfoot input#more').attr('disabled', false).val('Load More');
			}
		});
	}

	var timerID = window.setTimeout(update, 10000);
	
	$('table#bb tfoot input#more').click(function(){
		$('table#bb tfoot input#more').val('Loading More…');
		update($('table#bb tbody tr.thread').size() + 15);
	});
}
				
function toggleCheckbox(which) {
	document.getElementById(which).checked = !document.getElementById(which).checked;
}

function toggleCheckboxes(chkd) {
	for (var i = 0; i < document.frmUserList.elements.length; i++) {
		if (document.frmUserList.elements[i].name.indexOf("chk") != -1) document.frmUserList.elements[i].checked = chkd;
	}
}

function triggerEmailTo() {
	var oneFound = false;
	var arrEmails = new Array();
	var arrRecord = new Array();
	for (var i = 0; i < document.frmUserList.elements.length; i++) {
		if (document.frmUserList.elements[i].name.indexOf("chk") != -1) {
			if (document.frmUserList.elements[i].checked) {
				arrRecord = document.frmUserList.elements[i].name.split("_");
				if (arrRecord[2].length > 0) {
					oneFound = true;
					arrEmails[arrEmails.length] = arrRecord[2];
				}
			}
		}
	}
	
	if (oneFound) {
		location.href="mailto:" + arrEmails.join(";");
	} else {
		alert("No checkboxes are selected that correspond to email addresses!");
	}
}
				
function gotoTop() {
	if (navigator.platform != "Win32")
		scrollTo(0,0);
	else
	{
		var ts = 0;
		if (document.layers)
			ts = window.pageYOffset;
		else if (document.body.scrollTop)
			ts = document.body.scrollTop;
		else
			ts = window.pageYOffset;
		if (ts > 0)
		{
			var nv = (ts -5) * 0.2;
			if (nv < 1) nv = 1;
			scrollBy(0, -nv);
			setTimeout("gotoTop()", 20);
		}
	}
}

//calendar event
function popEvent(id) {
	var win = window.open("../calendar/event.php?id=" + id,"win","width=320,height=240,location=0,menubar=0,scrollbars=auto,resizeable=0,toolbar=0");
	win.opener = self;
	win.focus();
}

//search
function doSearch(form) {
    if (!form.q.value.length) return false;
    return true;
}
		    
window.onload = init;

function init() {
	if (typeof(setupPage) == "function") setupPage();
}

function isValidEffExp(effMonth, effDay, effYear, expMonth, expDay, expYear) {
	if (effYear > expYear) {
		return false;
	} else if (effYear == expYear) {
		if (effMonth > expMonth) {
			return false;
		} else if (effMonth == expMonth) {
			if (effDay > expDay) {
				return false;
			}
		}
	}
	return true;
}

function isDateAfterToday(month,day,year) {
	var date      = new Date();
	var thisMonth = date.getMonth() + 1;
	var thisDay   = date.getDate();
	var thisYear  = date.getYear();
	if (thisYear > year) {
		return false;
	} else if (thisYear == year) {
		if (thisMonth > month) {
			return false;
		} else if(thisMonth == month) {
			if (thisDay > day) {
			return false;
			}
		}
	}
	return true;
}


function showHint(s) {
  window.status = (s ? s : '');
  return true;
}

function isValidDate(month,day,year) {
    if (!isFinite(month) || !isFinite(day) || !isFinite(year)) return false;
    
    var isLeapYear;
    (year % 4) ? isLeapYear = false : isLeapYear = true;
    if ((year % 100) == 0) isLeapYear = false;
    if ((year % 400) == 0) isLeapYear = true;
    
    if (day > 31) return false;
    if (((month == 9) || (month == 4) || (month == 6) || (month == 11)) && (day > 30)) return false;
    if ((month == 2) &&  isLeapYear && (day > 29)) return false;
    if ((month == 2) && !isLeapYear && (day > 28)) return false;
    return true;
}

function msieversion() {
    var ua   = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");
    if ( msie > 0 ) {
		return parseInt(ua.substring(msie+5, ua.indexOf(".", msie)));
    } else {
		return 0;
    }
}

function showErrors(errors) {
	var error;
	if (errors.length == 0) return true;
	if (errors.length == 1) {
		error = "This form could not go through because " + errors[0] + ".  Please fix this before continuing.";
	} else {
		var numbers = new Array('two','three','four','five','six','seven','eight','nine');
		var errornumber = (errors.length < 10) ? numbers[errors.length-2] : errors.length;
		error = "This form could not go through because of the following " + errornumber + " errors:\n\n";
		for (var i = 0; i < errors.length; i++) {
			error += " - " + errors[i] + "\n";
		}
		error += "\nPlease fix before continuing.";
	}
	
	alert(error);
	return false;
}

function editNumeric(evt) {
	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {
		if (isIE4 == true) {
		} else {
			var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
	        var period  = (!shift && !ctrl && !alt && keycode == 46);
			if (period || digit) {
				return true;
			} else {
				return false;
			}
		}
	}
}

function editInteger(evt) {
	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {
		if (isIE4 == true) {
		} else {
			var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
			if (digit) {
				return true;
			} else {
				return false;
			}
		}
	}
}

function editSSN(evt) {
	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {
		if (isIE4 == true) {
		    var keycode = window.event.keyCode;
	        var pos     = this.value.length + 1;

	        if (pos == 1) return true;
	        if (pos == 2) return true;
	        if (pos == 3) return true;
	        if (pos == 4) return true;
	        if (pos == 5) return true;
	        if (pos == 6) return true;
	        if (pos == 7) return true;
	        if (pos == 8) return true;
	        if (pos == 9) return true;
	        if (pos == 10) return true;
	        if (pos == 11) return true;
	        if (keycode == 13) return true;
	        
	        return false;
		} else {
	        var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
	        var dash    = (!shift && !ctrl && !alt && keycode == 45);
	        var pos     = this.value.length + 1;

	        if (keycode == 13) return true; 
	        if (!dash && !digit) return false;
	        
	        
	        if (pos == 1  && digit) return true;
	        if (pos == 2  && digit) return true;
	        if (pos == 3  && digit) return true;
	        
	        if (pos == 4  && digit) { this.value += '-'; return true; }
	        if (pos == 4  && dash) return true;

	        if (pos == 5  && digit) return true;
	        if (pos == 6  && digit) return true;
	        
	        if (pos == 7  && digit) { this.value += '-'; return true; }
	        if (pos == 7  && dash) return true;
	        
	        if (pos == 8  && digit) return true;
	        if (pos == 9  && digit) return true;
	        if (pos == 10 && digit) return true;
	        if (pos == 11 && digit) return true;	        
	        return false;
		}
	}
}


function editTaxID(evt) {
	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {
		if (isIE4 == true) {
		    var keycode = window.event.keyCode;
	        var pos     = this.value.length + 1;

	        if (pos == 1) return true;
	        if (pos == 2) return true;
	        if (pos == 3) return true;
	        if (pos == 4) return true;
	        if (pos == 5) return true;
	        if (pos == 6) return true;
	        if (pos == 7) return true;
	        if (pos == 8) return true;
	        if (pos == 9) return true;
	        if (pos == 10) return true;
	        if (keycode == 13) return true;
	        
	        return false;
		} else {
	        var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
	        var dash    = (!shift && !ctrl && !alt && keycode == 45);
	        var pos     = this.value.length + 1;

	        if (keycode == 13) return true; 
	        if (!dash && !digit) return false;
	        
	        
	        if (pos == 1  && digit) return true;
	        if (pos == 2  && digit) return true;
	        
	        if (pos == 3  && digit) { this.value += '-'; return true; }
	        if (pos == 3  && dash) return true;
	       
	        if (pos == 4  && digit) return true;
	        if (pos == 5  && digit) return true;
	        if (pos == 6  && digit) return true;
	        if (pos == 7  && digit) return true;
	        if (pos == 8  && digit) return true;
	        if (pos == 9  && digit) return true;
	        if (pos == 10 && digit) return true;
	        return false;
		}
	}
}

function isTaxID() {
	var taxid    = this.value.replace(/\D/g, "");
	var valid     = true;

	if (!isFinite(taxid) || (taxid.length != 9)) {
		valid = false;
	}
	
	return valid;
}


function isSSN() {
	var social    = this.value.replace(/\D/g, "");

	if (!isFinite(social) || (social.length != 9)) return false;
	
	return true;
}

function editTime(evt) {
				var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
				if (isIE) {
				    var keycode = window.event.keyCode;
				    var shift   = window.event.shiftKey;
				    var ctrl    = window.event.ctrlKey;
				    var alt     = window.event.altKey;
				    var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
					var space   = (!shift && !ctrl && !alt && keycode == 32);
				    var dash    = (!shift && !ctrl && !alt && keycode == 45);
				    var pos     = this.value.length + 1;
				    var letter    = (!ctrl && !alt && ((keycode > 64 && keycode < 91) || (keycode > 96 && keycode < 123)));
					var colon   = (shift && !ctrl && !alt && keycode >= 48 && keycode == 58);
				    if (pos == 1  && digit && (keycode == 48 || keycode == 49)) return true;
				    if (pos == 2) {
						if ((this.value.substring(0,1) == 0) && digit) {
							return true;
						} else if ((this.value.substring(0,1) == 1) && digit && keycode < 51) {
							return true;
						}
					}
				    if (pos == 3  && colon) return true;
				    if (pos == 3  && digit) { this.value += ':'; return true; }
				    if (pos == 4  && digit && keycode < 54) return true;
				    if (pos == 5  && digit) return true;
				    if (pos == 6) {
						if (space) {
							return true;
						} else if (keycode == 97 || keycode == 65) {
							this.value += ' A';
							return false;
						} else if (keycode == 112 || keycode == 80) {
							this.value += ' P';
							return false;
						}
					}
					if (pos == 7 && (keycode == 97 || keycode == 65)) {
							this.value += 'A';
							return false;
					}
					if (pos == 7 && (keycode == 112 || keycode == 80)) {
							this.value += 'P';
							return false;
					}
					if (pos == 8 && (keycode == 109 || keycode == 77)) {
							this.value += 'M';
							return false;
					}
				    return false;
				}
			}			

function editDate(evt) {
	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE  = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {
	        if (isIE4 == true) {
		    var keycode = window.event.keyCode;
	        var pos     = this.value.length + 1;

	        if (pos == 1) return true;
	        if (pos == 2) return true;
	        if (pos == 3) return true;
	        if (pos == 4) return true;
	        if (pos == 5) return true;
	        if (pos == 6) return true;
	        if (pos == 7) return true;
	        if (pos == 8) return true;
	        if (pos == 9) return true;
	        if (pos == 10) return true;
	        if (pos == 11) return true;
	        if (keycode == 13) return true;
	        return false;
	} else {
	        var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
	        var slash   = (!shift && !ctrl && !alt && keycode == 47);
	        var pos     = this.value.length + 1;

	        if (keycode == 13 && keycode == 14) return true; 
	        if (!slash && !digit) return false;
       
	        
	        if (pos == 1  && digit) return true;
	        if (pos == 2  && digit) return true;
	        
	        if (pos == 3  && digit) { this.value += '/'; return true; }
	        if (pos == 3  && slash) return true;

	        if (pos == 4  && digit) return true;
	        if (pos == 5  && digit) return true;
	        
	        if (pos == 6  && digit) { this.value += '/'; return true; }
	        if (pos == 6  && slash) return true;
	        
	        if (pos == 7  && digit) return true;	        
	        if (pos == 8  && digit) return true;
	        if (pos == 9  && digit) return true;
	        if (pos == 10 && digit) return true;
	        return false;
		}
	}
}



function isDate() {

if(this.value == null){
	dateVal = ""}
if(this.value !=  null){
var dateVal = this.value.split("/");
} 
	
	var valid = true;
	if (dateVal.length == 3) {
		for (var i = 0; i < 3; i++) {
			if (isFinite(dateVal[i]) == false) {
				valid = false;
			}
		}
		if (dateVal[0] > 12) {
			valid = false;
		} else if (dateVal[1] > 31) {
			valid = false;
		} else if ((dateVal[2].length != 4) || (dateVal[2] > 2100) || (dateVal[2] < 1900)) {
			valid = false;
		}
	} else {
		valid = false;
	}
	if (valid) {
		if (dateVal[0].length == 1) {
			dateVal[0] = "0" + dateVal[0];
		}
		if (dateVal[1].length == 1) {
			dateVal[1] = "0" + dateVal[1];
		}
		var newvalue = dateVal[0] + "/" + dateVal[1] + "/" + dateVal[2];
		this.value = newvalue;
	}
	return valid;
}

function editPhone(evt) {

	var isNav = (navigator.appName.indexOf("Netscape") != -1);
	var isIE = (navigator.appName.indexOf("Microsoft") != -1);
	var isIE4 = (msieversion() <=4);

	if (isIE) {

	        if (isIE4 == true) {
	        var keycode = window.event.keyCode;
	        var pos     = this.value.length + 1;

	        if (pos == 1) return true;
	        if (pos == 2) return true;
	        if (pos == 3) return true;
	        if (pos == 4) return true;
	        if (pos == 5) return true;
	        if (pos == 6) return true;
	        if (pos == 7) return true;
	        if (pos == 8) return true;
	        if (pos == 9) return true;
	        if (pos == 10) return true;
	        if (pos == 11) return true;
	        if (pos == 12) return true;
	        if (pos == 13) return true;
	        if (pos == 14) return true;
	        if (keycode == 13) return true;

	        return false;

	        } else {

	        var keycode = window.event.keyCode;
	        var shift   = window.event.shiftKey;
	        var ctrl    = window.event.ctrlKey;
	        var alt     = window.event.altKey;
	        var digit   = (!shift && !ctrl && !alt && keycode >= 48 && keycode <= 57);
	        var lparen  = (shift  && !ctrl && !alt && keycode == 40);
	        var rparen  = (shift  && !ctrl && !alt && keycode == 41);
	        var space   = (!shift && !ctrl && !alt && keycode == 32);
	        var dash    = (!shift && !ctrl && !alt && keycode == 45);
	        var slash   = (!shift && !ctrl && !alt && keycode == 47);

	        var pos     = this.value.length + 1;
	        
	        //alert(keycode);
	        if ((keycode == 13) || (keycode == 8) || (keycode == 46)) return true; 
			
			
	        if (!lparen && !rparen && !space && !dash && !digit) return false;

	        if (pos == 1  && lparen) return true;
	        if (pos == 1  && digit) { this.value = '('; return true; }

	        if (pos == 2  && digit) return true;
	        if (pos == 3  && digit) return true;
	        if (pos == 4  && digit) return true;

	        if (pos == 5  && rparen) { this.value += ') '; return false; }
	        if (pos == 5  && dash)   { this.value += ') '; return false; }
	        if (pos == 5  && slash)  { this.value += ') '; return false; }
	        if (pos == 5  && space)  { this.value += ') '; return false; }
	        if (pos == 5  && digit)  { this.value += ') '; return true; }

	        if (pos == 6  && space) return true;
	        if (pos == 6  && digit) { this.value += ' '; return true; }

	        if (pos == 7  && digit) return true;
	        if (pos == 8  && digit) return true;
	        if (pos == 9  && digit) return true;

	        if (pos == 10 && dash) return true;
	        if (pos == 10 && space) { this.value += '-'; return false; }
	        if (pos == 10 && digit) { this.value += '-'; return true; }

	        if (pos == 11 && digit) return true;
	        if (pos == 12 && digit) return true;
	        if (pos == 13 && digit) return true;
	        if (pos == 14 && digit) return true;

	        return false;
        }
	}
}
function isPhone() {
	var phone     = this.value.replace(/\D/g, "");
	var area_code = phone.slice(0,3);
	var exchange  = phone.slice(3,6);
	var valid     = true;

	if ((area_code == 000) || (area_code == 555) || (exchange == 555) || (exchange == 000) || !isFinite(phone) || (phone.length != 10)) {
		valid = false;
	}
	if (this.value == '') return true;
	return valid;
}

function isNumeric() {
	return (isFinite(this.value));
}

function isInteger() {
	return ((!isFinite(this.value)) || (this.value != Math.round(this.value)));
}