<?php

return function ($landbotScript, $data, $params) {
  return "<script src=" . $landbotScript . "></script>
    <script>
        var myLandbotPopup = new LandbotPopup({
        index: '". get_object_vars($data)['url'] ."". $params ."',
        });
    </script>";
};