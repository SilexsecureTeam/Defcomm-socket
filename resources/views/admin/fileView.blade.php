<!DOCTYPE html>
<html>

<head>
    <title>Embed PDF with Watermark</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <style>
        #pdf-container {
            width: 100%;
            height: auto;
            overflow: auto;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .pdf-page {
            display: block;
            margin: 10px auto;
            border: 1px solid #000;
            /* Adding border to each page */
            padding: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            /* Optional: Adding a shadow for better visibility */
            position: relative;
            /* Required for watermark positioning */
        }
    </style>
</head>

<body>
    <div id="pdf-container"></div>
    <script>
        var url = "{{ url('/secure')}}/{{$file->file}}";

        // Initialize the PDF.js library
        var pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

        // Asynchronous download of PDF
        var loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            var pdfContainer = document.getElementById('pdf-container');

            // Loop through each page and render it
            for (var pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                pdf.getPage(pageNum).then(function(page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({
                        scale: scale
                    });

                    // Prepare canvas using PDF page dimensions
                    var canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page'; // Add the class for styling
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext).promise.then(function() {
                        pdfContainer.appendChild(canvas);

                        // Adding multiple watermarks
                        var watermarkText = "View by {{$user->name}}";
                        context.font = "30px Arial";
                        context.fillStyle = "#C1C1C0";
                        context.globalAlpha = 0.2; // Faint watermark
                        context.textAlign = "center";
                        context.textBaseline = "middle";

                        // context.fillText(watermarkText, canvas.width / 2, canvas.height / 2);

                        // Define the grid size for watermarks
                        var gridSpacingX = 200;
                        var gridSpacingY = 150;

                        // Loop to place watermarks in a grid
                        for (var x = 0; x < canvas.width; x += gridSpacingX) {
                            for (var y = 0; y < canvas.height; y += gridSpacingY) {
                                context.fillText(watermarkText, x, y);
                            }
                        }
                    });
                });
            }
        }, function(reason) {
            console.error(reason);
        });
    </script>
</body>

</html>