Set objXMLHttp = CreateObject("MSXML2.ServerXMLHTTP.6.0")
Set objADOStream = CreateObject("ADODB.Stream")

' Get command line arguments
strPHPURL = WScript.Arguments(0)
strPHPPath = WScript.Arguments(1)
strMariaDBURL = WScript.Arguments(2)
strMariaDBPath = WScript.Arguments(3)

' Download PHP
WScript.Echo "Downloading PHP from: " & strPHPURL
objXMLHttp.Open "GET", strPHPURL, False
objXMLHttp.Send

If objXMLHttp.Status = 200 Then
    objADOStream.Open
    objADOStream.Type = 1
    objADOStream.Write objXMLHttp.ResponseBody
    objADOStream.SaveToFile strPHPPath
    objADOStream.Close
    WScript.Echo "PHP downloaded successfully"
Else
    WScript.Echo "ERROR: PHP download failed (HTTP " & objXMLHttp.Status & ")"
    WScript.Quit 1
End If

' Download MariaDB
WScript.Echo "Downloading MariaDB from: " & strMariaDBURL
objXMLHttp.Open "GET", strMariaDBURL, False
objXMLHttp.Send

If objXMLHttp.Status = 200 Then
    objADOStream.Open
    objADOStream.Type = 1
    objADOStream.Write objXMLHttp.ResponseBody
    objADOStream.SaveToFile strMariaDBPath
    objADOStream.Close
    WScript.Echo "MariaDB downloaded successfully"
Else
    WScript.Echo "ERROR: MariaDB download failed (HTTP " & objXMLHttp.Status & ")"
    WScript.Quit 1
End If
