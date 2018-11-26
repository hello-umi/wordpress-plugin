var moreOptionsCheck = {
  hideBackground: false,
  hideHeader: false
}

var displayFormat = 'POPUP'

document.addEventListener('DOMContentLoaded', function() {

  addClassDisplayFormat(displayFormat);
    
  document.querySelector('#landbot-admin-form').addEventListener('submit', function (e){

    e.preventDefault();

    var formData = new FormData();

    formData.append('authorization', document.getElementById('authorization').value);
    formData.append('hideBackground', moreOptionsCheck.hideBackground);
    formData.append('hideHeader', moreOptionsCheck.hideHeader);
    formData.append('displayFormat', displayFormat);

    if(displayFormat === 'EMBED') {
      var widgetHeight = document.getElementById('widget-height').value !== '' && !isNaN(document.getElementById('widget-height').value) ? document.getElementById('widget-height').value : 500; 
      formData.append('widgetHeight', widgetHeight);
    }
    
    formData.append('action', 'store_admin_data');
    formData.append('security', landbot_exchanger._nonce);

    if(document.getElementById('authorization').value !== '') {
      getData('POST', landbot_exchanger.ajax_url, formData).then(function (response) {
        console.log(response);   
      })
    }

  })

}, false);


function getData (method, url, params) {

  return new Promise( function (resolve, reject) {

	  var xhr = new XMLHttpRequest();

    xhr.open(method, url, true);

	  xhr.addEventListener('load', function (e) {

	    var response = {
	      status: e.target.status
	    }

	    resolve(response);
	  })

	  xhr.addEventListener('error', function (e) {
	    reject(e.target);
	  })

    xhr.send(params);
  
  })
}

function checkMoreOptions (element, option) {
  element.classList.toggle('left');
  moreOptionsCheck[option] = !moreOptionsCheck[option];
}

function checkDisplayFormat (option) {
  removeClassDisplayFormat();
  addClassDisplayFormat(option);
  showWidgetHeight(option);
  displayFormat = option;
}

function addClassDisplayFormat(format) {
  var elements = document.querySelectorAll('.square-display-format');
  
  elements.forEach(function (element) {
    if(element.innerText.replace(/\n/ig, '') === format) {
      element.classList.toggle('border-color');
      element.childNodes.forEach(function (childElement) {
        if(childElement.innerText === format) childElement.classList.toggle('display-format-color-selected');
      })
    }
  })
}

function removeClassDisplayFormat() {
  var borderColor = document.querySelector('.border-color')
  var formatColorSelected = document.querySelector('.display-format-color-selected')
  
  borderColor.classList.remove('border-color')
  formatColorSelected.classList.remove('display-format-color-selected')
}

function widgetHeightElement() {
  var widgetHeight = '<div>Widget height (pixels)</div><div><input name="widget-height" id="widget-height" class="regular-text" placeholder="Default value 500 pixels" type="text" /></div>';

  return widgetHeight;
}

function showWidgetHeight(option) {
  if(option === 'EMBED') {
    var element = document.getElementById('embed-selected');
    element.innerHTML = widgetHeightElement(); 
  } else {
    var element = document.getElementById('embed-selected');
    element.innerHTML = '';
  }
}