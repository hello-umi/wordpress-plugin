<?php

return function ($landbotScript, $data, $params) {
  return "<script src=" . $landbotScript . "></script>
    <script>
      var myLandbotLivechat = new LandbotLivechat({
        index: '". get_object_vars($data)['url'] ."". $params ."',
        });
    </script>";
};