var moreOptionsCheck = {
  hideBackground: false,
  hideHeader: false
}

var displayFormat = 'POPUP';

document.addEventListener('DOMContentLoaded', function() {

  if(landbot_constants.token) {
    var tokenElement = document.getElementById('authorization');
    tokenElement.value = landbot_constants.token;
  }

  if(landbot_constants.displayFormat) {
    displayFormat = landbot_constants.displayFormat.toUpperCase();
    showWidgetHeight(displayFormat);
  }

  if(landbot_constants.displayFormat === 'embed' && landbot_constants.widgetHeight) {
    var widgetElement = document.getElementById('widget-height');
    if(widgetElement) widgetElement.value = landbot_constants.widgetHeight;
  }

  if(parseInt(landbot_constants.hideBackground)) {
    var hideBackgroundElement = document.getElementById('hideBackground');
    checkMoreOptions(hideBackgroundElement, 'hideBackground');
  }

  if(parseInt(landbot_constants.hideHeader)) {
    var hideHeaderElement = document.getElementById('hideHeader');
    checkMoreOptions(hideHeaderElement, 'hideHeader')
  }

  console.log(landbot_constants)


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
    formData.append('security', landbot_constants._nonce);
    
    if(document.getElementById('authorization').value !== '') {
      getData('POST', landbot_constants.ajax_url, formData).then(function (response) {
        console.log(response)
        errorHandler(response);   
      })
    } else {
      showAlertMessage('Token is mandatory field.', '#c74d4d');
    }

  })

}, false);

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