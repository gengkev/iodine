//Ideal situation:
// This is where chat for intranet will be implemented. It's basically a javascript IRC client
// Which connects to a specific server, allowing it to communicate in a way that's already pretty
// well-defined. Also, it saves us the effort of designing a better protocol, not to mention
// getting iodine up to the speed necessary to run an AJAX chat in real time.
//Current situation:
// It's an AJAX chat client within intranet.

var container = document.getElementById("chat_container");
container.innerHTML="Loading.";
var chatarea = document.getElementById("chat_area");

/* Null Communication */
function incomingMessage(obj,event) {
	return;
}
function sendMessage(message) {
	return;
}
/* End Null Communication */

/* Testing Communication with alert() */
/*
function incomingMessage(obj,event) {
	if((event.keyCode ? event.keyCode : event.which ? event.which : event.charCode)!=13)
		return true;
	responseRecieved(obj.value);
	return false;
}
function sendMessage(message) {
	alert(message);
}
*/
/* End Testing transmission */

// Because we'll use the irc protocol for communication, but don't have a specified tunnel system,
// we define several methods of tunneling the connections here, and decide which one to use based
// on the target browser and configuration.
/* AJAX transmission */

var ajaxChatRequest;

try {
	ajaxChatRequest= new XMLHttpRequest();
} catch (e) {
	try {
		ajaxChatRequest = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			ajaxChatRequest = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(e) {
			alert("Sorry, won't work. Going back to normal iodine");
			window.location="../";
		}
	}
}

ff35check=/Firefox/g; // There's ajax issues with this under certain conditions.
if(ff35check.test(navigator.userAgent)) {
	ajaxChatRequest.onload=ajaxChatRequest.onerror=ajaxChatRequest.onabort = function(){
		responseRecieved(ajaxChatRequest.responseText);
	}
} else {
	ajaxChatRequest.onreadystatechange = function(){
		if(ajaxChatRequest.readyState == 4){
			responseRecieved(ajaxChatRequest.responseText);
		}
	}
}

function sendMessage(message) {
	if(message.length<=0)
		return;
	message="message="+encodeURI(message);
	ajaxChatRequest.open("POST",i2root+"fastajax/chat.php5",false);
	ajaxChatRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajaxChatRequest.setRequestHeader("Content-Length", message.length);
	ajaxChatRequest.setRequestHeader("Connection", "close");
	ajaxChatRequest.send(message);
}

function lookformessages() {
	ajaxChatRequest.open("GET",i2root+"fastajax/chat.php5",false);
	ajaxChatRequest.send(null);
	setTimeout(lookformessages,1000);
}
setTimeout(lookformessages,1000);

/* End AJAX */

/* Begin IRC functions */

//var senderindicatorregex = new RegExp("something that matches the strings:  :user!username@host.webaddress");

function initSession() {
	container.innerHTML="Welcome "+name;
	//addchatwindow("#tjhsst");
	
	var inbox = document.createElement('textarea');
	inbox.style.width="100%";
	inbox.setAttribute('onkeypress','incomingMessage(this,event)');
	inbox.id="#server";
	container.appendChild(inbox);
	
	sendMessage("");//Wake up the relay, tell it about our presence
	sendMessage("NICK i" + userid);
	sendMessage("USER i" + userid + " 4 * : " +fullname);// Do this to prevent errors with the second one coming back before the first one can be handled.
	joinChannel("#newchan");
	sendMessage("WHO dmorris");
	window.onunload=function() {sendMessage("QUIT")};;
}
initSession();
function joinChannel(channame) {
	sendMessage("JOIN "+channame);
	addchatwindow(channame);
}
function responseRecieved(responseContent) {
	if(responseContent.substr(0,4).toUpperCase()=="PING")
	{
		sendMessage("PONG"+responseContent.substr(4));
		return;
	}
	if(responseContent.substr(0,4).toUpperCase()=="PONG")
		return;
	//if(responseContent.length>0)
	//	alert("recieved the following: "+responseContent);
	
	//TODO: Figure out who sender is, what channel, and put into correct chat window.
	//var correctwindow="disp-"+senderid;
	//var correctchat="disp-"+"onlyone";
	//var textdivobj;
	var parts=responseContent.split(" ");
	if(parts[1] && parts[1].toUpperCase() == "PRIVMSG") {
		//It's a message!
		if(parts[2]==("i"+userid)) {
			//Private message to us
			printChat(stripUser(parts[0]),parts.slice(3).join(" ").substr(1),stripUser(parts[0]));
		} else {
			printChat(parts[2],parts.slice(3).join(" ").substr(1),stripUser(parts[0]));
		}
	} else if(parts[0].toUpperCase() == "PRIVMSG") {
		//It's a message that we just sent
		if(parts[1]=="i"+username.substring(0,7)) {
			//Private message to us, from us
			//Kinda silly, but w/e
			printChat(parts[0],parts.slice(2).join(" ").substr(1),"i"+userid);
		} else {
			printChat(parts[1],parts.slice(2).join(" ").substr(1),"i"+userid);
		}
	//} else if(parts[
	} else {
		printChat("#server",responseContent,"Server");
	}
}
function formatAndSend(textobject,event) {
	if((event.keyCode ? event.keyCode : event.which ? event.which : event.charCode)!=13)
		return true;
	if(textobject.value.length <= 0)
		return false;
	var message = "PRIVMSG ";
	message += textobject.id.substr(13);
	message += " :";
	message += textobject.value;
	sendMessage(message);
	setTimeout(function(){textobject.value=""},1);
	return false;
}
function lookupUID(uidstr) {
	if(uidstr.length==0)
		return "NULL_STRING_PASSED";
	sendMessage("WHO "+uidstr);
}
function stripUser(str) {
	return str.substr(1,str.indexOf("!")-1);
}
function addchatwindow(targetname) {
	// targetname is the name of the channel or user with which the chat window should correspond
	if(document.getElementById("chat_bigholderbox"+targetname))
		return; //Already exists
	// Here comes a big formatting block. It might help to move some of this to CSS later on.
	var bigholder=document.createElement("div");
	bigholder.id="chat_bigholderbox"+targetname;
	bigholder.className="bigholder";
	var holder=document.createElement("div");
	holder.style.position="absolute";
	holder.style.bottom="0";
	holder.style.width="250px";
	holder.style.visibility="visible";
	holder.setAttribute('class',"intrabox");// Cheap way to get the right border styling
	holder.style.marginBottom="0px";
	holder.style.backgroundColor="#ffffff";
	holder.style.color="#000000";
	/*holder.style.backgroundColor="#ffffff";
	holder.style.borderWidth="2px";
	holder.style.borderStyle="Solid";*/
	var header=document.createElement("div");
	header.setAttribute('class',"boxheader");
	header.innerHTML="Chat: "+targetname;
	header.id="chat_header"+targetname;
	var minimizebutton=document.createElement("span");
	minimizebutton.innerHTML="_";
	minimizebutton.style.CSSFloat="right";
	minimizebutton.style.position="absolute";
	minimizebutton.style.right="16px";
	minimizebutton.setAttribute('onclick','minimizetoggle("chat_windowarea'+targetname+'")');
	header.appendChild(minimizebutton);
	var closebutton=document.createElement("span");
	closebutton.innerHTML="X";
	closebutton.style.CSSFloat="right";
	closebutton.style.position="absolute";
	closebutton.style.right="3px";
	closebutton.setAttribute('onclick','tempobject=document.getElementById("chat_bigholderbox'+targetname+'");tempobject.parentNode.removeChild(tempobject)');
	header.appendChild(closebutton);
	var otherarea=document.createElement("div");
	otherarea.id="chat_windowarea"+targetname;
	var text=document.createElement("div");
	text.id="chat_textbox"+targetname;
	text.style.overflowY="scroll";
	text.style.height="180px";
	/*var textinside=document.createTextNode("Chat created");
	text.appendChild(textinside);*/
	var input=document.createElement("textarea");
	input.id="chat_inputbox"+targetname;
	input.style.width="98%";
	input.setAttribute('onkeypress','formatAndSend(this,event)');
	holder.appendChild(header);
	otherarea.appendChild(text);
	otherarea.appendChild(input);
	holder.appendChild(otherarea);
	//var bigholder=document.createElement("div");
	bigholder.appendChild(holder);
	chatarea.appendChild(bigholder);
}
function minimizetoggle(boxname) { // Exactly what's implied by the function name: it toggles whether a box is minimized or not.
	box=document.getElementById(boxname);
	if(box.style.display=="none") {
		box.style.display="block";
	} else {
		box.style.display="none";
	}
}
function hasText(channel) {
	return document.getElementById("chat_textbox"+channel).innerHTML.length>0;
}
function printChat(channel,message,user) {
	if(message.length == 0)
		return;
	addchatwindow(channel);
	if(hasText(channel)) {
		var textbox=document.getElementById("chat_textbox"+channel);
		if(textbox.scrollTop==textbox.scrollHeight) {
			if(textbox.lastUser==user)
				textbox.lastChild.innerHTML += "<br />" + message;
			else {
				textbox.lastUser = user;
				var newcontainer = document.createElement("div");
				newcontainer.innerHTML="<b>"+user+":</b> ";
				textbox.appendChild(newcontainer);
				textbox.lastChild.innerHTML += message;
			}
			textbox.scrollTop=textbox.scrollHeight;
		} else {
			if(textbox.lastUser==user)
				textbox.lastChild.innerHTML += "<br />" + message;
			else {
				textbox.lastUser = user;
				var newcontainer = document.createElement("div");
				newcontainer.innerHTML="<b>"+user+":</b> ";
				textbox.appendChild(newcontainer);
				textbox.lastChild.innerHTML += message;
			}
		}
	} else {
		var textbox=document.getElementById("chat_textbox"+channel);
		textbox.lastUser = user;
		var newcontainer = document.createElement("div");
		newcontainer.innerHTML="<b>"+user+":</b> ";
		textbox.appendChild(newcontainer);
		textbox.lastChild.innerHTML += message;
	}
}
