<div class="container mt-4">
    <div class="card p-4">
        <h4>Category Details</h4>
        <hr>
        <div class="mb-2"><strong>Name:</strong> {{ $category->name }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $category->description ?? 'N/A' }}</div>
    </div>
</div>
