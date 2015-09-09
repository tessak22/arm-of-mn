function rsm_check_card(what)
{
	what.value = what.value.replace(/[^0-9]+/g, '');
}

function rsm_check_year(what)
{
	if (what.value.length == 2)
		what.value = '20' + what.value;
	var currDate = new Date();
	if (what.value < currDate.getFullYear())
		what.value = currDate.getFullYear();
}

function rsm_check_authorize(url)
{
	pay_button = document.getElementById('rsm_pay_button');
	pay_button.style.visibility = 'hidden';
	
	loading = document.getElementById('rsm_loading');
	loading.style.display = '';
	
	var xmlHttp = rsm_get_xml_http_object();
	//var url 	= 'index.php?option=com_rsmembership&plugin_task=authorize';
	
	var has_error = false;
	
	var cc_number = document.getElementById('rsm_cc_number');
	/*
	var cc_length = cc_number.value.length;
	if (cc_length < 14 || cc_length > 19)
	{
		if (cc_number.className.indexOf(' rsm_error') == -1) cc_number.className += " rsm_error";
		has_error = true;
	}
	else
		cc_number.className = cc_number.className.replace(new RegExp(" rsm_error\\b"), '');
	*/
	var csc_number = document.getElementById('rsm_csc_number');
	if (csc_number.value.length < 3)
	{
		if (csc_number.className.indexOf(' rsm_error') == -1) csc_number.className += " rsm_error";
		has_error = true;
	}
	else
		csc_number.className = csc_number.className.replace(new RegExp(" rsm_error\\b"), '');
	
	var cc_exp_mm  = document.getElementById('rsm_cc_exp_mm');
	
	var cc_exp_yy  = document.getElementById('rsm_cc_exp_yy');
	if (cc_exp_yy.value.length != 4)
	{
		if (cc_exp_yy.className.indexOf(' rsm_error') == -1) cc_exp_yy.className += " rsm_error";
		has_error = true;
	}
	else
		cc_exp_yy.className = cc_exp_yy.className.replace(new RegExp(" rsm_error\\b"), '');
	
	var cc_fname   = document.getElementById('rsm_cc_fname');
	if (cc_fname.value.length == 0)
	{
		if (cc_fname.className.indexOf(' rsm_error') == -1) cc_fname.className += " rsm_error";
		has_error = true;
	}
	else
		cc_fname.className = cc_fname.className.replace(new RegExp(" rsm_error\\b"), '');
		
	var cc_lname   = document.getElementById('rsm_cc_lname');
	if (cc_lname.value.length == 0)
	{
		if (cc_lname.className.indexOf(' rsm_error') == -1) cc_lname.className += " rsm_error";
		has_error = true;
	}
	else
		cc_lname.className = cc_lname.className.replace(new RegExp(" rsm_error\\b"), '');
	
	var response = document.getElementById('rsm_response');
	if (has_error)
		response.innerHTML = rsm_get_error_message(0);
	
	if (!has_error)
	{
		params  = 'cc_number=' + cc_number.value;
		params += '&csc_number=' + csc_number.value;
		params += '&cc_exp_mm=' + cc_exp_mm.value;
		params += '&cc_exp_yy=' + cc_exp_yy.value;
		params += '&cc_fname=' + encodeURIComponent(cc_fname.value);
		params += '&cc_lname=' + encodeURIComponent(cc_lname.value);
		params += '&membership_id=' + document.getElementById('membership_id').value;
		xmlHttp.open("POST", url, false);
		
		//Send the proper header information along with the request
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlHttp.setRequestHeader("Content-length", params.length);
		xmlHttp.setRequestHeader("Connection", "close");

		xmlHttp.send(params);
		if (xmlHttp.responseText.indexOf('RSM_AUTHORIZE_OK') > -1)
		{
			return true;
		}
		else if (xmlHttp.responseText.indexOf('RSM_SESSION_END') > -1)
		{
			return true;
		}
		else
			response.innerHTML = xmlHttp.responseText;
	}
	
	loading.style.display = 'none';
	pay_button.style.visibility = 'visible';
	
	return false;
}

var rsm_tooltip=function(){
	var id = 'rsm_tt';
	var top = 3;
	var left = 3;
	var maxw = 400;
	var speed = 10;
	var timer = 20;
	var endalpha = 95;
	var alpha = 0;
	var tt,t,c,b,h;
	var ie = document.all ? true : false;
	return{
		show:function(v,w){
			if(tt == null){
				tt = document.createElement('div');
				tt.setAttribute('id',id);
				t = document.createElement('div');
				t.setAttribute('id',id + 'top');
				c = document.createElement('div');
				c.setAttribute('id',id + 'cont');
				b = document.createElement('div');
				b.setAttribute('id',id + 'bot');
				tt.appendChild(t);
				tt.appendChild(c);
				tt.appendChild(b);
				document.body.appendChild(tt);
				tt.style.opacity = 0;
				tt.style.filter = 'alpha(opacity=0)';
				document.onmousemove = this.pos;
			}
			tt.style.display = 'block';
			c.innerHTML = document.getElementById(v).innerHTML;
			tt.style.width = w ? w + 'px' : 'auto';
			if(!w && ie){
				t.style.display = 'none';
				b.style.display = 'none';
				tt.style.width = tt.offsetWidth;
				t.style.display = 'block';
				b.style.display = 'block';
			}
			if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
			h = parseInt(tt.offsetHeight) + top;
			clearInterval(tt.timer);
			tt.timer = setInterval(function(){rsm_tooltip.fade(1)},timer);
		},
		pos:function(e){
			var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
			var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
			tt.style.top = (u - h) + 'px';
			tt.style.left = (l + left) + 'px';
		},
		fade:function(d){
			var a = alpha;
			if((a != endalpha && d == 1) || (a != 0 && d == -1)){
				var i = speed;
				if(endalpha - a < speed && d == 1){
					i = endalpha - a;
				}else if(alpha < speed && d == -1){
					i = a;
				}
				alpha = a + (i * d);
				tt.style.opacity = alpha * .01;
				tt.style.filter = 'alpha(opacity=' + alpha + ')';
			}else{
				clearInterval(tt.timer);
				if(d == -1){tt.style.display = 'none'}
			}
		},
		hide:function(){
			clearInterval(tt.timer);
			tt.timer = setInterval(function(){rsm_tooltip.fade(-1)},timer);
		}
	};
}();