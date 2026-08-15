import sys
from PIL import Image

def process_logo(input_path, output_path):
    # Open image
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()
    
    # We want to make the dark green background transparent
    # Let's sample the background color from the top-left corner
    bg_color = data[0]
    
    new_data = []
    # Tolerance for background color matching
    tolerance = 20
    
    # Bounding box variables to find the non-background area
    min_x = img.width
    min_y = img.height
    max_x = 0
    max_y = 0
    
    for y in range(img.height):
        for x in range(img.width):
            item = data[y * img.width + x]
            # Check if current pixel is close to background color
            if (abs(item[0] - bg_color[0]) < tolerance and
                abs(item[1] - bg_color[1]) < tolerance and
                abs(item[2] - bg_color[2]) < tolerance):
                new_data.append((255, 255, 255, 0)) # Transparent
            else:
                new_data.append(item)
                # Update bounding box
                if x < min_x: min_x = x
                if x > max_x: max_x = x
                if y < min_y: min_y = y
                if y > max_y: max_y = y
                
    img.putdata(new_data)
    
    # Crop to bounding box
    if max_x >= min_x and max_y >= min_y:
        # Add a little padding (20px)
        padding = 20
        left = max(0, min_x - padding)
        top = max(0, min_y - padding)
        right = min(img.width, max_x + padding)
        bottom = min(img.height, max_y + padding)
        
        img = img.crop((left, top, right, bottom))
        
        # Make it square by expanding the canvas
        width, height = img.size
        new_size = max(width, height)
        
        square_img = Image.new("RGBA", (new_size, new_size), (255, 255, 255, 0))
        
        # Paste in center
        paste_x = (new_size - width) // 2
        paste_y = (new_size - height) // 2
        square_img.paste(img, (paste_x, paste_y))
        
        square_img.save(output_path, "PNG")
        print("Logo cropped, squared, and background removed successfully!")
    else:
        print("Could not find the logo text/icon.")
        img.save(output_path, "PNG")

if __name__ == "__main__":
    process_logo("public/images/sikarya-logo.png", "public/images/sikarya-logo.png")
