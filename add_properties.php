<!-- add_property.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Property</title>
</head>
<body>
    <h1>Submit New Property</h1>
    <form action="submit_property.php" method="POST" enctype="multipart/form-data">
        <!-- Property Name -->
        <div class="fl">
            <label>Property Name</label>
            <input type="text" name="property_name" required />
        </div>
        <!-- Property Type -->
        <div class="fl">
            <label>Property Type</label>
            <input type="text" name="property_type" required />
        </div>
        <!-- Address -->
        <div class="fl">
            <label>Address</label>
            <input type="text" name="address" required />
        </div>
        <!-- Units -->
        <div class="fl">
            <label>Units</label>
            <input type="number" name="units" required />
        </div>
        <!-- Rent Amount -->
        <div class="fl">
            <label>Rent Amount</label>
            <input type="number" step="0.01" name="rent_amount" required />
        </div>
        <!-- Transaction Type -->
        <div class="fl">
            <label>Transaction Type</label>
            <select name="purpose" required>
                <option value="">Select Type</option>
                <option value="rent">For Rent</option>
                <option value="buy">For Buy</option>
                <option value="lease">For Lease</option>
            </select>
        </div>
        <!-- Bedrooms -->
        <div class="fl">
            <label>Bedrooms</label>
            <input type="number" name="bedrooms" required />
        </div>
        <!-- Size in sqft -->
        <div class="fl">
            <label>Size (sqft)</label>
            <input type="number" name="size_sqft" required />
        </div>
        <!-- Amenities -->
        <div class="fl">
            <label>Amenities</label>
            <textarea name="amenities"></textarea>
        </div>
        <!-- Latitude & Longitude -->
        <div class="fl">
            <label>Latitude</label>
            <input type="text" name="latitude" required />
        </div>
        <div class="fl">
            <label>Longitude</label>
            <input type="text" name="longitude" required />
        </div>
        <!-- Description -->
        <div class="fl">
            <label>Description</label>
            <textarea name="description"></textarea>
        </div>
        <!-- Commission Rate -->
        <div class="fl">
            <label>Commission Rate</label>
            <input type="number" step="0.01" name="commission_rate" required />
        </div>
        <!-- Commission Percentage -->
        <div class="fl">
            <label>Commission Percentage</label>
            <input type="number" step="0.01" name="commission_percentage" required />
        </div>
        <!-- Image Upload -->
        <div class="fl">
            <label>Property Image</label>
            <input type="file" name="property_image" accept="image/*" required />
        </div>
        <!-- Submit Button -->
        <div class="fl">
            <button type="submit">Submit Property</button>
        </div>
    </form>
</body>
</html>