<?php
session_start();
$numTrees = isset($_SESSION['numTrees']) ? $_SESSION['numTrees'] : 0;
$drawing = isset($_SESSION['drawing']) ? $_SESSION['drawing'] : '';
$iconSize = isset($_SESSION['iconSize']) ? $_SESSION['iconSize'] : 50; // Default icon size
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Trees</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Your Drawing with Trees</h1>
    <canvas id="drawingCanvas" width="800" height="600"></canvas>
    <br>
    
    <!-- Input for number of trees -->
    <label for="numTrees">Number of Trees:</label>
    <input type="number" id="numTrees" value="<?php echo $numTrees; ?>" min="0" />
    
    <!-- Input for size of tree icons -->
    <label for="iconSize">Size of Tree Icons (px):</label>
    <input type="number" id="iconSize" value="<?php echo $iconSize; ?>" min="10" />

    <p id="maxTreesCount">Max Trees Allowed: 0</p> <!-- Display max trees count -->
    <p id="actualTreesCount">Actual Trees Placed: 0</p> <!-- Display actual trees count -->
    <h2>Excess Trees</h2>
    <p id="excessTreesCount">Excess Trees: 0</p> <!-- Display excess trees count -->
    
    <button onclick="window.location.href='draw.html'">Back to Drawing</button>

    <script>
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        const drawing = localStorage.getItem('drawing');
        const img = new Image();
        let treeIcons = [];
        let selectedTree = null;
        let offsetX, offsetY;

        img.onload = function() {
            ctx.drawImage(img, 0, 0);
            updateMaxTrees(); // Update max trees when the image loads
            drawTrees(); // Initial draw
        };

        img.src = drawing;

        function updateMaxTrees() {
            const drawingBounds = getDrawingBounds(ctx);
            const iconSize = parseInt(document.getElementById('iconSize').value); // Get icon size from input

            // Calculate maximum number of trees that can fit within the bounds
            const maxTreesInBounds = Math.floor((drawingBounds.right - drawingBounds.left) / iconSize) *
                                     Math.floor((drawingBounds.bottom - drawingBounds.top) / iconSize);

            // Update the max trees count display
            document.getElementById('maxTreesCount').innerText = `Max Trees Allowed: ${maxTreesInBounds}`;

            // Set the input value to the minimum of current value and max allowed
            const currentValue = parseInt(document.getElementById('numTrees').value);
            if (currentValue > maxTreesInBounds) {
                document.getElementById('numTrees').value = maxTreesInBounds;
            }
        }

        function drawTrees() {
            const numTrees = parseInt(document.getElementById('numTrees').value); // Get the number of trees from input
            const iconSize = parseInt(document.getElementById('iconSize').value); // Get icon size from input
            const drawingBounds = getDrawingBounds(ctx);
            const treeIcon = new Image();
            treeIcon.src = 'tree_icon2.png'; // Use the local file path

            treeIcon.onload = function() {
                ctx.drawImage(img, 0, 0); // Redraw the original image before adding trees

                treeIcons = []; // Clear previous tree icons

                let actualTreesPlaced = 0;
                let excessTrees = 0;

                for (let i = 0; i < numTrees; i++) {
                    let x, y;
                    let overlap = true;

                    while (overlap) {
                        // Adjust the random position to ensure the icon fits within the bounds
                        x = drawingBounds.left + Math.random() * (drawingBounds.right - drawingBounds.left - iconSize);
                        y = drawingBounds.top + Math.random() * (drawingBounds.bottom - drawingBounds.top - iconSize);
                        overlap = false;

                        // Check for overlap with existing drawing
                        const imageData = ctx.getImageData(x, y, iconSize, iconSize).data;
                        for (let j = 0; j < imageData.length; j += 4) {
                            if (imageData[j + 3] > 0) { // Check for non-transparent pixels
                                overlap = true;
                                break;
                            }
                        }
                    }

                    // Check if the position is within the drawing bounds
                    if (x < drawingBounds.left || x + iconSize > drawingBounds.right ||
                        y < drawingBounds.top || y + iconSize > drawingBounds.bottom) {
                        excessTrees++; // Increment excess trees if out of bounds
                    } else {
                        treeIcons.push({ x, y, iconSize }); // Store the position and size of the tree icon
                        ctx.drawImage(treeIcon, x, y, iconSize, iconSize); // Draw tree icon
                        actualTreesPlaced++; // Count the actual trees placed
                    }
                }

                // Update the displayed count of actual trees placed and excess trees
                document.getElementById('actualTreesCount').innerText = `Actual Trees Placed: ${actualTreesPlaced}`;
                document.getElementById('excessTreesCount').innerText = `Excess Trees: ${excessTrees}`;
            };
        }

        function getDrawingBounds(ctx) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            let left = canvas.width, right = 0, top = canvas.height, bottom = 0;

            for (let y = 0; y < canvas.height; y++) {
                for (let x = 0; x < canvas.width; x++) {
                    const index = (y * canvas.width + x) * 4;
                    if (imageData[index + 3] > 0) { // Check for non-transparent pixels
                        left = Math.min(left, x);
                        right = Math.max(right, x);
                        top = Math.min(top, y);
                        bottom = Math.max(bottom, y);
                    }
                }
            }

            return { left, right, top, bottom };
        }

        // Dragging functionality
        canvas.addEventListener('mousedown', function(event) {
            const mousePos = getMousePos(canvas, event);
            selectedTree = treeIcons.find(tree => 
                mousePos.x >= tree.x && mousePos.x <= tree.x + tree.iconSize &&
                mousePos.y >= tree.y && mousePos.y <= tree.y + tree.iconSize
            );

            if (selectedTree) {
                offsetX = mousePos.x - selectedTree.x;
                offsetY = mousePos.y - selectedTree.y;
            }
        });

        canvas.addEventListener('mousemove', function(event) {
            if (selectedTree) {
                const mousePos = getMousePos(canvas, event);
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas
                ctx.drawImage(img, 0, 0); // Redraw the background image
                drawExistingTrees(); // Redraw existing trees
                selectedTree.x = mousePos.x - offsetX; // Update the position of the selected tree
                selectedTree.y = mousePos.y - offsetY;
                ctx.drawImage(treeIcon, selectedTree.x, selectedTree.y, selectedTree.iconSize, selectedTree.iconSize); // Draw the tree at the new position
            }
        });

        canvas.addEventListener('mouseup', function() {
            selectedTree = null; // Deselect the tree
        });

        function getMousePos(canvas, event) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top
            };
        }

        function drawExistingTrees() {
            const treeIcon = new Image();
            treeIcon.src = 'tree_icon2.png'; // Use the local file path
            treeIcon.onload = function() {
                treeIcons.forEach(tree => {
                    ctx.drawImage(treeIcon, tree.x, tree.y, tree.iconSize, tree.iconSize);
                });
            };
        }

        // Event listener for input change
        document.getElementById('numTrees').addEventListener('input', function() {
            updateMaxTrees(); // Update max trees when input changes
            drawTrees(); // Redraw trees based on the new input
        });

        document.getElementById('iconSize').addEventListener('input', function() {
            drawTrees(); // Redraw trees based on the new icon size
        });
    </script>
</body>
</html>
