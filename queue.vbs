CreateObject("WScript.Shell").Run Chr(34) & CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName) & "\queue.bat" & Chr(34), 0
