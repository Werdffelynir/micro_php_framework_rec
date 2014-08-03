<div class="grid-3 first">
    <div class="edit-menu">

        <ul>
            <li><a href="<?=Rec::$url.'admin';?>">Create New Record</a></li>
        </ul>

        <h4>Main records</h4>
        <ol>
            <?php foreach($listEditMenuMain as $editmenu): ?>
                <li> <a href="<?=Rec::$url.'admin/edit/'.$editmenu['id']?>"><?=$editmenu['title']?></a></li>
            <?php endforeach; ?>
        </ol>

        <h4>Blog records</h4>
        <ol>
            <?php foreach($listEditMenuBlog as $editmenu): ?>
                <li><a href="<?=Rec::$url.'admin/edit/'.$editmenu['id']?>"><?=$editmenu['title']?></a></li>
            <?php endforeach; ?>
        </ol>

    </div>
</div>


<div class="grid-9">
    <div class="admin-form">
        <form id="form" name="form" action="<?= Rec::$url ?>admin" method="post" autocomplete="off">

            <input name="title" type="text" value="<?=$itemEdit['title']?>"/>
            <br/>
            <select name="type">
                <?php if($itemEdit['type']=='blog'): ?>
                    <option value="blog" selected >blog</option>
                    <option value="main" >main</option>
                <?php else: ?>
                    <option value="blog" >blog</option>
                    <option value="main" selected >main</option>
                <?php endif;?>
            </select>
            <br/>

            <?php

            ?>
            <textarea class="ckeditor" name="text" id="">
                <?php

                echo htmlspecialchars($itemEdit['text']);

                ?>
            </textarea>

            <input name="id" hidden type="text" value="<?=$itemEdit['id']?>"/>
            <input name="typeEdit" hidden type="text" value="<?=$typeEdit?>"/>

            <input type="submit" id="submit" name="save" value="Save"/>
            <input type="submit" id="submit" name="delete" value="Delete"/>

        </form>
    </div>
</div>

<script type="application/javascript">

    if(typeof CKEDITOR !== 'undefined') {

        CKEDITOR.editorConfig = function( config )
        {

        };

        CKEDITOR.replace('text', {
            filebrowserBrowseUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/browse.php?type=files',
            filebrowserImageBrowseUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/browse.php?type=images',
            filebrowserFlashBrowseUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/browse.php?type=flash',
            filebrowserUploadUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/upload.php?type=files',
            filebrowserImageUploadUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/upload.php?type=images',
            filebrowserFlashUploadUrl:'<?= Rec::$url ?>public/ckeditor/kcfinder/upload.php?type=flash'
        });





    } // END CKEDITOR

</script>





























