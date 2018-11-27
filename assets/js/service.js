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