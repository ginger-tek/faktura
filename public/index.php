<?php

require_once '../src/App.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faktura</title>
  <link rel="icon" href="/favicon.ico">
  <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@2.1.1/css/pico.jade.min.css">
  <link rel="stylesheet" href="https://unpkg.com/@ginger-tek/picocss-extras@1.1.9/picocss-extras.css">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>
  <div id="app" style="display:flex;flex-direction:column;min-height:100dvh">
    <div aria-busy="true"></div>
  </div>
  <script src="https://unpkg.com/vue@3.5.38/dist/vue.global.js"></script>
  <script src="https://unpkg.com/vue-router@5.1.0/dist/vue-router.global.js"></script>
  <script src="/assets/main.js" type="module"></script>
</body>

</html>