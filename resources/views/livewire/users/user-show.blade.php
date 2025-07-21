<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Name</label>
                        <input class="form-control" type="text" wire:model="name" placeholder="{{ $user->name }}" >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Email</label>
                        <input class="form-control" type="email" wire:model="email" placeholder="{{ $user->email }}" >
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
