<?php

return function ($landbotScript, $data, $params) {
  return "<script src=" . $landbotScript . "></script> 
    <div id='landbot-1543425349304' style='width: 100%; height: ".get_object_vars($data)['widgetHeight']."px; position: absolute; top: ".get_object_vars($data)['positionTop']."px;'></div>
    <script>
      var myLandbotFrame = new LandbotFrameWidget({
        container: '#landbot-1543425349304',
        index: '". get_object_vars($data)['url'] ."". $params ."',
      });
    </script>";
};