<?php

return function ($landbotScript, $data, $params) {
  return "<script src=" . $landbotScript . "></script>
    <script>
      var myLandbotFullpage = new LandbotFullpage({
        index: '". get_object_vars($data)['url'] ."". $params ."',
      });
    </script>";
};