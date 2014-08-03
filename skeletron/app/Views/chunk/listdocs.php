

<?php foreach($listdocs as $doc): ?>

    <div class="item">

      <h2>
          <a href="<?php echo Rec::$url.'doc/'.$doc['id'] ?>">
          <?php echo $doc['title']; ?>
          </a>
      </h2>

      <?php echo $this->limitWords($doc['text'],50,' ...<br><br>'); ?>

      <div class="item-panel">
        <a href="<?php echo Rec::$url.'doc/'.$doc['id'] ?>">Открыть</a> &nbsp;
        <span>Обновленно: <?php echo $doc['time']; ?></span>
      </div>

    </div>

<?php endforeach; ?>
