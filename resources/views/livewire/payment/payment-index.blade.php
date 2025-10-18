@section('page-title', 'Payments')

 <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Payment List</h4>

                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target="#createRoleModal">
                                + Create payment
                            </button>

                        </div>
                        <div class="modal fade" id="createRoleModal" tabindex="-1"
                            aria-labelledby="createRoleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title " id="createRoleModalLabel">Create payment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        @livewire('payment.payment-create')
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="card-body">
                            <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ID & Name</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                        <th>Paid At</th>
                                        <th>Trasaction Id</th>
                                        <th style="width: 80px; min-width: 80px;">Action</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    @foreach ($payments as $index => $payment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $payment->service_request_id }}:
                                                <span
                                                    class="text-muted">{{ $payment->serviceRequest->service->name ?? 'N/A' }}</span>
                                            </td>

                                            <td>ZMK {{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span
                                                    class="
                        @if ($payment->status == 'accepted') badge rounded-pill badge-soft-success
                        @elseif($payment->status == 'completed') badge rounded-pill badge-soft-primary
                        @elseif($payment->status == 'in_progress') badge rounded-pill badge-soft-info
                        @elseif($payment->status == 'cancelled') badge rounded-pill badge-soft-danger
                        @else badge rounded-pill badge-soft-warning @endif">{{ ucfirst($payment->status) }}</span>
                                            </td>
                                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                            <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->toDayDateTimeString() : 'Not Paid' }}
                                            </td>
                                            <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#showRoleModal{{ $payment->id }}">
                                                            Show
                                                        </a>


                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editRoleModal{{ $payment->id }}">
                                                            Edit
                                                        </a>
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="#"
                                                            data-confirm-event="deletePayment"
                                                            data-confirm-id="{{ $payment->id }}"
                                                            data-confirm-title="Delete payment?"
                                                            data-confirm-message="This payment record will be removed permanently."
                                                            data-confirm-button="Yes, delete it">
                                                            Delete
                                                        </a>
                                                        {{-- @can('show.payments') --}}
                                                        {{-- <li><a class="dropdown-item"
                                                 href="{{ route('payments.show', $payment->id) }}">Show</a></li> --}}
                                                        {{-- @endcan --}}
                                                        {{-- @can('edit.payments') --}}
                                                        {{-- <li><a class="dropdown-item"
                                                 href="{{ route('payments.edit', $payment->id) }}">Edit</a></li> --}}
                                                        {{-- @endcan --}}
                                                        {{-- @can('delete.payments') --}}
                                                        {{-- <li><a class="dropdown-item" wire:click="delete({{ $payment->id }})"
                                                 wire:confirm="Are you Sure you want to delete payment">Delete</a></li> --}}
                                                        {{-- @endcan --}}
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editRoleModal{{ $payment->id }}" tabindex="-1"
                                            aria-labelledby="editRoleModalLabel{{ $payment->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editRoleModalLabel{{ $payment->id }}">
                                                            Edit payment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        @livewire('payment.payment-edit', ['id' => $payment->id], key('payment-edit-' . $payment->id))
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- Show Modal for Each Role -->
                                        <div class="modal fade" id="showRoleModal{{ $payment->id }}" tabindex="-1"
                                            aria-labelledby="showRoleModalLabel{{ $payment->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="showRoleModalLabel{{ $payment->id }}">
                                                            Show payment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        @livewire('payment.payment-show', ['id' => $payment->id], key('payment-show-' . $payment->id))
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>
                    </div>

                    <!-- end cardaa -->
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div>
    </div>
