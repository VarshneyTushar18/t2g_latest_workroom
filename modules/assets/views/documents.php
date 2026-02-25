<?php init_head(); ?>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

<style>
    /* Add your custom styles here */
    .light-img {
        width: 200px;
        height: 200px;
        margin-bottom: 4px;
    }

    .pdf-img {
        width: 100px;
        height: 125px;

        margin-top: 40px;

    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <h4 class="no-margin font-bold"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo "Uploaded Documents"; ?></h4>
                            <hr>


                            <!-- <div class="lightgallery"> -->
                            <?php
                            foreach ($images as $image) {
                                $imgexp = explode('.', $image);

                                $imgExt = array(
                                    'jpg',
                                    'jpeg',
                                    'png',
                                    'gif'
                                );

                                if (in_array(strtolower(end($imgexp)), $imgExt)) {
                                    echo '<div class="col-md-4  "><a href="/modules/assets/uploads/' . $image . '" data-fancybox="gallery">
                                        <img class = "light-img "src = "/modules/assets/uploads/' . $image . '"></a> </div>';
                                } else {
                                    echo '<div class="col-md-4 "><a href="/modules/assets/uploads/' . $image . '" data-fancybox="gallery"><img class = " pdf-img " src = "https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/PDF_file_icon.svg/800px-PDF_file_icon.svg.png" ></a></div>';
                                    // echo ' <a href="/modules/assets/uploads/' . $image . '" data-fancybox="gallery"><iframe src="/modules/assets/uploads/' . $image . '"
                                    // width="500" ></a>';
                                }
                            }
                            ?>
                            <!-- </div> -->

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php init_tail(); ?>
</body>

</html>

<script type="text/javascript">
    Fancybox.bind("[data-fancybox]", {
        // Your custom options
    });
</script>