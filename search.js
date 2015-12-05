document.addEventListener('DOMContentLoaded', function() {
  'use strict'; // We're all grownups now, let's use strict mode: https://goo.gl/xmOUmj
  
  //Hide the First Child(Error Message)
  var ErrorContainer = document.getElementsByTagName('li')[0];
  ErrorContainer.style.display = 'none';
  
  
  //Switch to Tile
	var items = document.getElementsByTagName('li');
	for(var i=1; i<items.length; i++)
	{
		items[i].getElementsByTagName('h3')[0].style.display = 'none';
		//items[i].getElementsByTagName('h3')[0].style.display = 'inline';
		items[i].style.display = 'inline';
		window.display = 'inline';
	}
});

document.getElementById("search_text").addEventListener("input", function(){
	var items = document.getElementsByTagName('li');
	var search = document.getElementById('search_text').value;
	var success = false;
	items[0].style.display = 'none';
	for(var i=1; i<items.length; i++)
	{
		if (items[i].getElementsByTagName('h3')[0].innerHTML.toLowerCase().indexOf(search.toLowerCase()) == -1)
		{
			items[i].style.display = 'none';
		}
		else
		{
			items[i].style.display = window.display;
			success = true;
		}
		
	}
	if (success == false)
	{
		items[0].style.display = window.display;
		items[0].firstChild.nodeValue = search + ' not found.';
	}
});

document.getElementById("show_all_button").addEventListener("click", function(){
	var items = document.getElementsByTagName('li');
	document.getElementById('search_text').value = "";
	items[0].style.display = 'none';
	for(var i=1; i<items.length; i++)
	{
		items[i].style.display = window.display;
	}
});

document.getElementById("tile").addEventListener("click", function(){
	var items = document.getElementsByTagName('li');
	for(var i=1; i<items.length; i++)
	{
		items[i].getElementsByTagName('h3')[0].style.display = 'none';
		//items[i].getElementsByTagName('h3')[0].style.display = 'inline';
		items[i].style.display = 'inline';
		window.display = 'inline';
	}
});

document.getElementById("list").addEventListener("click", function(){
	var items = document.getElementsByTagName('li');
	for(var i=1; i<items.length; i++)
	{
		items[i].getElementsByTagName('h3')[0].style.display = 'inline';
		//items[i].getElementsByTagName('h3')[0].style.display = 'block';
		items[i].style.display = 'block';
		window.display = 'block';
	}
});