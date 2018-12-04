var moreOptionsCheck = {
  hideBackground: false,
  hideHeader: false
}

var displayFormat = 'POPUP';

var pagesID = [];

document.addEventListener('DOMContentLoaded', function() {

  var elementQuerySelector = document.querySelector('#landbot-admin-form');

  if(elementQuerySelector) {

    setInitialConfiguration();

    setSelectedPagesValue();
    renderListsPages();
    setSelectedPagesValue();

    document.querySelector('#landbot-admin-form').addEventListener('submit', function (e){

      e.preventDefault();
  
      var formData = new FormData();
  
      formData.append('authorization', document.getElementById('authorization').value);
      formData.append('hideBackground', moreOptionsCheck.hideBackground);
      formData.append('hideHeader', moreOptionsCheck.hideHeader);
      formData.append('displayFormat', displayFormat);
  
      if(displayFormat === 'EMBED') {
        formData.append('widgetHeight', checkMoreoptionsCorrectValue('widget-height'));
        formData.append('positionTop', checkMoreoptionsCorrectValue('position-top'));
      }
  
      formData.append('action', 'store_admin_data');
      formData.append('security', landbot_constants._nonce);
      formData.append('pagesSelected', pagesID.join(','));
      
      if(document.getElementById('authorization').value !== '') {
        if(pagesID.length > 0) {
          getData('POST', landbot_constants.ajax_url, formData).then(function (response) {
            errorHandler(response);   
          })
        } else {
          showAlertMessage('Select one or more pages.', '#c74d4d');
        }      
      } else {
        showAlertMessage('URL is mandatory field.', '#c74d4d');
      }
  
    })

  }
    
}, false);

function checkMoreOptions (option) {
  var elementOption = document.getElementById(option);
  if(elementOption) {
    elementOption.classList.toggle('left');
    moreOptionsCheck[option] = !moreOptionsCheck[option];
  }
  
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
      if(element.innerText.replace(/\n/ig, '') === 'EMBED') {
        createElement();
      } else {
        var code = document.getElementById('code');
        if(code) {
          var scriptCode = document.getElementById('script-code');
          scriptCode.style.display = 'none';
          code.remove(code);
        }
      } 
      
      element.classList.toggle('border-color');

      element.childNodes.forEach(function (childElement) {
        if(childElement.innerText === format) childElement.classList.toggle('display-format-color-selected');
      })
    }
  })
}

function createElement() {
  var codeElement = document.createElement('div');
  var scriptCode = document.getElementById('script-code');
  scriptCode.style.display = 'initial';
  codeElement.innerHTML = codeContent();
  scriptCode.appendChild(codeElement);
  var contentElement = document.getElementById('code');
  codeElement.appendChild(contentElement)
  
  elements().forEach(function (element) { 
    var paragraph = document.createElement('p');
    if(element.includes('position: absolute')) {
      paragraph.innerText = '<div id="landbot-1543425349304" style="width: 100%; height: 500px;"></div>'
    } else {
      paragraph.innerText = element;
    }
    contentElement.appendChild(paragraph);
  }); 
}

function elements() {
  return landbot_constants.embed.split('\n').map(function (string) {
    return string.trim();
  })
}

function codeContent() {
  return '<div id="code" class="script-section"></div>';
}
  
function removeClassDisplayFormat() {
  var borderColor = document.querySelector('.border-color')
  var formatColorSelected = document.querySelector('.display-format-color-selected')
    
  borderColor.classList.remove('border-color')
  formatColorSelected.classList.remove('display-format-color-selected')
}
  
function widgetHeightElement() {
  var widgetHeight = '<div>Widget height (pixels)</div><div><input name="widget-height" id="widget-height" class="regular-text" placeholder="Default value 500 pixels" type="text" /></div>';
  var postionRespectTop = '<div>Widget position respect top page (pixels)</div><div><input name="position-top" id="position-top" class="regular-text" placeholder="Default value 500 pixels" type="text" /></div>';
  
  return widgetHeight + postionRespectTop;
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

function setInitialConfiguration() {

  if(landbot_constants.url) {
    var urlElement = document.getElementById('authorization');
    urlElement.value = landbot_constants.url;
  }

  if(landbot_constants.displayFormat) {
    displayFormat = landbot_constants.displayFormat.toUpperCase();
    showWidgetHeight(displayFormat);
  }

  if(landbot_constants.displayFormat === 'embed' && landbot_constants.widgetHeight) {
    var widgetElement = document.getElementById('widget-height');
    var positionTop = document.getElementById('position-top');
    if(widgetElement) widgetElement.value = landbot_constants.widgetHeight;
    if(positionTop) positionTop.value = landbot_constants.positionTop;
  }

  if(parseInt(landbot_constants.hideBackground)) {
    checkMoreOptions('hideBackground');
  }

  if(parseInt(landbot_constants.hideHeader)) {
    checkMoreOptions('hideHeader');
  }

  addClassDisplayFormat(displayFormat);
}

function checkMoreoptionsCorrectValue(elementName) {
  return document.getElementById(elementName).value !== '' 
         && !isNaN(document.getElementById(elementName).value) 
         && parseInt(document.getElementById(elementName).value) >= 0 ? document.getElementById(elementName).value : 500;
}

function renderListsPages() {
  var listPagesElement = document.getElementById('list-pages');

  var pages = landbot_constants.pages.map(function(page) {
    return pageElement(page);
  });

  pages.unshift('<li><input onclick="checkPage(this)" type="checkbox" value="home"/> Home page </li>');

  listPagesElement.innerHTML = pages.join('');

}

function pageElement(page) {
  return '<li><input onclick="checkPage(this)" type="checkbox" value="' + page.ID + '" /> ' + page.post_title + '</li>';
}

function checkPage(element) {
  if(pagesID.includes(element.value)) {
    var index = pagesID.indexOf(element.value);
    pagesID.splice(index, 1)
  } else {
    pagesID.push(element.value);
  }
}

function setSelectedPagesValue() {
  var listPages = document.querySelectorAll('#list-pages li input');
  listPages.forEach(function (element) {
    landbot_constants.pagesSelected.forEach(function(page) {
      if(element.value === page) {
        element.checked = true;
        pagesID.push(page);
      } 
    });
  });
}