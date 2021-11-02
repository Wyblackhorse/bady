//
global_requestAddress = "";

//临时token
// global_token = "ZQBfdzecQbjkK8DSiGX6Stid7R4JBQez";

//登录接口
global_request_login="http://8.136.97.179:9501/admin/login";

//用户管理接口
global_request_memberInformation="http://8.136.97.179:9501/admin/memberInformation";

//账号管理接口
global_request_addAccount="http://8.136.97.179:9501/admin/addAccount";

//宠物管理接口
global_request_addBaby="http://8.136.97.179:9501/admin/addBaby";

//统计管理列表接口
global_request_get_statistics="http://8.136.97.179:9501/admin/get_statistics";

//统计管理添加接口
global_request_addTodayTotal="http://8.136.97.179:9501/admin/addTodayTotal";

//统计管理添加接口
global_request_addStatistics="http://8.136.97.179:9501/admin/addStatistics";

//个人统计管理删除接口
global_request_updateStatistics="http://8.136.97.179:9501/admin/updateStatistics";

//改变总价接口
global_request_changePrice="http://8.136.97.179:9501/admin/changePrice";

//更新最新数据(最准确的)
global_request_GetTodayStatistics="http://8.136.97.179:9501/admin/GetTodayStatistics";




var getRootPath_webStr = getRootPath_web();

//获取目录路径方法
function getRootPath_web() {

		//获取当前网址，如： http://localhost:8888/eeeeeee/aaaa/vvvv.html
		var curWwwPath = window.document.location.href;
		//获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
		var pathName = window.document.location.pathname;
		var pos = curWwwPath.indexOf(pathName);
		//获取主机地址，如： http://localhost:8888
		var localhostPaht = curWwwPath.substring(0, pos);
		//获取带"/"的项目名，如：/abcd
		var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);

		// return (localhostPaht + projectName);


		// console.log("当前网址:"+curWwwPath);
		// console.log("主机地址后的目录:"+pos+"----"+pathName);
		// console.log("主机地址:"+localhostPaht);
		// console.log("项目名:"+projectName);


		return projectName;
}



//时间戳转日期时间型工具类
function formatDateTime(inputTime) {
	var date = new Date(inputTime);
	var y = date.getFullYear();
	var m = date.getMonth() + 1;
	m = m < 10 ? ('0' + m) : m;
	var d = date.getDate();
	d = d < 10 ? ('0' + d) : d;
	var h = date.getHours();
	h = h < 10 ? ('0' + h) : h;
	var minute = date.getMinutes();
	var second = date.getSeconds();
	minute = minute < 10 ? ('0' + minute) : minute;
	second = second < 10 ? ('0' + second) : second;
	return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;
}


function toDecimal2(x) {//金额处理两位小数点
	var f = parseFloat(x);
	if (isNaN(f)) {
		return false;
	}
	var f = Math.round(x*100)/100;
	var s = f.toString();
	var rs = s.indexOf('.');
	if (rs < 0) {
		rs = s.length;
		s += '.';
	}
	while (s.length <= rs + 2) {
		s += '0';
	}
	return s;
}


/**
 * 数字转整数 如 100000 转为10万
 * @param {需要转化的数} num
 * @param {需要保留的小数位数} point
 */
function tranNumber(num, point) {



	let numStr = num.toString()

	// console.log(numStr.length);
	// 一万以内直接返回
	if (numStr.length <=4) {
		return numStr;
	}
	//大于6位数是十万 (以10W分割 10W以下全部显示)
	else if (numStr.length > 4) {
		let decimal = numStr.substring(numStr.length - 4, numStr.length - 4 + point)
		// return parseFloat(parseInt(num / 10000) + ‘.’ + decimal) + ‘万’;
		return parseFloat(parseInt(num / 10000) + '.' + decimal) + '万';
	}
}




//验证是否为数字
function isNumber(value) { //验证是否为数字

	var patrn = /^(-)?\d+(\.\d+)?$/;

	if (patrn.exec(value) == null || value == "") {
		return false

	} else {
		return true

	}

}

/**
 * 获取指定的URL参数值
 * URL:http://www.xxxxx.com/index?name=tyler
 * 参数：paramName URL参数
 * 调用方法:getParam("name")
 * 返回值:tyler
 */
function getParam(paramName) {
	paramValue = "", isFound = !1;
	if (this.location.search.indexOf("?") == 0 && this.location.search.indexOf("=") > 1) {
		arrSource = unescape(this.location.search).substring(1, this.location.search.length).split("&"), i = 0;
		while (i < arrSource.length && !isFound) arrSource[i].indexOf("=") > 0 && arrSource[i].split("=")[0].toLowerCase() == paramName.toLowerCase() && (paramValue = arrSource[i].split("=")[1], isFound = !0), i++
	}
	return paramValue == "" && (paramValue = null), paramValue
}



/*
* 获得时间差,时间格式为 年-月-日 小时:分钟:秒 或者 年/月/日 小时：分钟：秒
* 其中，年月日为全格式，例如 ： 2010-10-12 01:00:00
* 返回精度为：秒，分，小时，天
*/

function GetDateDiff(startTime, endTime, diffType) {
	//将xxxx-xx-xx的时间格式，转换为 xxxx/xx/xx的格式
	startTime = startTime.replace(/\-/g, "/");
	endTime = endTime.replace(/\-/g, "/");

	//将计算间隔类性字符转换为小写
	diffType = diffType.toLowerCase();
	var sTime = new Date(startTime);      //开始时间
	var eTime = new Date(endTime);  //结束时间
	//作为除数的数字
	var divNum = 1;
	switch (diffType) {
		case "second":
			divNum = 1000;
			break;
		case "minute":
			divNum = 1000 * 60;
			break;
		case "hour":
			divNum = 1000 * 3600;
			break;
		case "day":
			divNum = 1000 * 3600 * 24;
			break;
		default:
			break;
	}
	return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));
}


//别人封装的时间工具
function transferTime(cTime) {
	var jsonDate = new Date(parseInt(cTime));
	Date.prototype.format = function (format) {
		var o = {
			"y+": this.getFullYear(),
			"M+": this.getMonth() + 1,
			"d+": this.getDate(),
			"h+": this.getHours(),
			"m+": this.getMinutes(),
			"s+": this.getSeconds()
		};

		if (/(y+)/.test(format)) {
			format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
		}

		for (var k in o) {
			if (new RegExp("(" + k + ")").test(format)) {
				format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("" + o[k]).substr("" + o[k].length));
			}
		}
		return format;
	};
	var newDate = jsonDate.format("yyyy-MM-dd hh:mm:ss");
	return newDate
}

function getNowFormatDate() {
	var date = new Date();
	var seperator1 = "-";
	var year = date.getFullYear();
	var month = date.getMonth() + 1;
	var strDate = date.getDate();
	if (month >= 1 && month <= 9) {
		month = "0" + month;
	}
	if (strDate >= 0 && strDate <= 9) {
		strDate = "0" + strDate;
	}
	var currentdate = year + seperator1 + month + seperator1 + strDate;
	return currentdate;
}
