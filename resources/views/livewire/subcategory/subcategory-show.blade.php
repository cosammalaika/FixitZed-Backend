<div class="container mt-4">
    <div class="card p-4">
        <h4>Subcategory Details</h4>
        <hr>

        <div class="mb-2"><strong>Category:</strong> {{ $subcategory->category->name ?? 'N/A' }}</div>
        <div class="mb-2"><strong>Name:</strong> {{ $subcategory->name }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $subcategory->description ?? 'N/A' }}</div>
    </div>
</div>
