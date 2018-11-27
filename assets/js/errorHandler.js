function errorHandler(response) {
  if(response.status === 200) {
    showAlertMessage('Configuration saved.', '#4dc753');
  }
  
  if(response.status === 500 || response.status === 400) {
    showAlertMessage('Incorrect params or error with server.', '#c74d4d');
  }
}

function alertMessage(message) {
  var alertMessage = message;
  return alertMessage;
}
    
function showAlertMessage(message, color) {
  var element = document.getElementById('alert-message');
  element.innerText = '';
  element.innerHTML = alertMessage(message); 
  element.style.color = color;
}