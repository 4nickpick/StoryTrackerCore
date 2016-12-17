<%
function mobile_device_detect (url)
	user_agent = Request.ServerVariables("HTTP_USER_AGENT")
	 
	mobile_browser = 0
	 
	Set Regex = New RegExp
	With Regex
	   .Pattern = "(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|windows ce|pda|mobile|mini|palm)"
	   .IgnoreCase = True
	   .Global = True
	End With
	 
	match = Regex.Test(user_agent)
	 
	If match Then mobile_browser = mobile_browser+1
	 
	If InStr(Request.ServerVariables("HTTP_ACCEPT"), "application/vnd.wap.xhtml+xml") Or Not IsEmpty(Request.ServerVariables("HTTP_X_PROFILE")) Or Not IsEmpty(Request.ServerVariables("HTTP_PROFILE")) Then
	   mobile_browser = mobile_browser+1
	end If
	 
	mobile_agents = Array("w3c ", "acs-", "alav", "alca", "amoi", "audi", "avan", "benq", "bird", "blac", "blaz", "brew", "cell", "cldc", "cmd-", "dang", "doco", "eric", "hipt", "inno", "ipaq", "java", "jigs", "kddi", "keji", "leno", "lg-c", "lg-d", "lg-g", "lge-", "maui", "maxo", "midp", "mits", "mmef", "mobi", "mot-", "moto", "mwbp", "nec-", "newt", "noki", "oper", "palm", "pana", "pant", "phil", "play", "port", "prox", "qwap", "sage", "sams", "sany", "sch-", "sec-", "send", "seri", "sgh-", "shar", "sie-", "siem", "smal", "smar", "sony", "sph-", "symb", "t-mo", "teli", "tim-", "tosh", "tsm-", "upg1", "upsi", "vk-v", "voda", "wap-", "wapa", "wapi", "wapp", "wapr", "webc", "winw", "winw", "xda", "xda-")
	size = Ubound(mobile_agents)
	mobile_ua = LCase(Left(user_agent, 4))
	 
	For i=0 To size
	   If mobile_agents(i) = mobile_ua Then
		  mobile_browser = mobile_browser+1
		  Exit For
	   End If
	Next
	 
	 
	If mobile_browser>0 Then
	   Response.Redirect(url)
	End If
end function
 
%>
