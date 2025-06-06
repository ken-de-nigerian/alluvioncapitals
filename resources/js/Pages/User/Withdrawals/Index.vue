<script setup lang="ts">
    import {Head, usePage, useForm} from "@inertiajs/vue3";
    import { computed, ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    import axios, { CancelTokenSource } from "axios";
    import { route } from "ziggy-js";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import ChoicesSelect from "../../../Components/Forms/ChoicesSelect.vue";
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    const page = usePage();

    interface Bank {
        code: string;
        name: string;
    }

    interface AccountDetails {
        account_number: string;
        account_name: string;
        bank_code: string;
        bank_name: string;
    }

    const props = defineProps<{
        user: { balance: number };
        withdrawal_details: object | null;
        withdrawals: Object
        banks: Bank[];
    }>();

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    // Refs for form fields (Add Payment Method)
    const selectedBankCode = ref('');
    const accountNumber = ref('');
    const accountName = ref('');
    const resolvedBankName = ref('');
    const isLoading = ref(false);
    const errorMessage = ref('');
    const successMessage = ref('');
    let cancelTokenSource: CancelTokenSource | null = null;

    // Refs for withdrawal form
    const withdrawalErrorMessage = ref('');
    const withdrawalSuccessMessage = ref('');

    // Inertia forms
    const accountForm = useForm({
        bank_code: '',
        bank_name: '',
        account_number: '',
        account_name: ''
    });

    const withdrawalForm = useForm({
        amount: 0
    });

    // Convert banks to options format for ChoicesSelect
    const bankOptions = computed(() => {
        return props.banks.map(bank => ({
            value: bank.code,
            label: bank.name
        }));
    });

    // Validate account number
    const validateAccountNumber = (): boolean => {
        if (!accountNumber.value || accountNumber.value.length < 10 || !/^\d+$/.test(accountNumber.value)) {
            errorMessage.value = 'Please enter a valid account number (minimum 10 digits)';
            return false;
        }
        return true;
    };

    // Validate withdrawal amount
    const validateWithdrawalAmount = (): boolean => {
        if (!withdrawalForm.amount || withdrawalForm.amount <= 0) {
            withdrawalErrorMessage.value = 'Please enter a valid withdrawal amount';
            return false;
        }
        if (withdrawalForm.amount > props.user.balance) {
            withdrawalErrorMessage.value = 'Withdrawal amount cannot exceed your balance';
            return false;
        }
        return true;
    };

    // Cancel pending request
    const cancelPendingRequest = () => {
        if (cancelTokenSource) {
            cancelTokenSource.cancel('Operation canceled due to new request');
            cancelTokenSource = null;
        }
    };

    // Resolve account details
    const resolveAccountDetails = async () => {
        if (!selectedBankCode.value || !validateAccountNumber()) {
            return;
        }

        cancelPendingRequest();
        isLoading.value = true;
        errorMessage.value = '';
        successMessage.value = '';

        try {
            cancelTokenSource = axios.CancelToken.source();

            const response = await axios.post(route('user.payments.resolve.account'), {
                bank_code: selectedBankCode.value,
                account_number: accountNumber.value
            }, {
                cancelToken: cancelTokenSource.token
            });

            if (response.data.success) {
                accountName.value = response.data.data.account_name;
                resolvedBankName.value = props.banks.find(bank => bank.code === selectedBankCode.value)?.name || '';
                successMessage.value = `Account Name: ${accountName.value}`;

                // Update Inertia form data
                accountForm.bank_code = selectedBankCode.value;
                accountForm.bank_name = resolvedBankName.value;
                accountForm.account_number = accountNumber.value;
                accountForm.account_name = accountName.value;
            } else {
                errorMessage.value = response.data.error || 'Failed to resolve account details';
            }
        } catch (error) {
            if (axios.isCancel(error)) {
                console.log('Request canceled:', error.message);
            } else if (error.response) {
                errorMessage.value = error.response.data.error || 'Error resolving account details';
            } else {
                errorMessage.value = 'Network error occurred. Please try again.';
            }
        } finally {
            isLoading.value = false;
            cancelTokenSource = null;
        }
    };

    // Debounced version of resolveAccountDetails
    const debouncedResolve = debounce(resolveAccountDetails, 1000);

    // Watch for changes in bank selection or account number
    watch([selectedBankCode, accountNumber], () => {
        errorMessage.value = '';
        successMessage.value = '';
        accountForm.reset();

        if (selectedBankCode.value && accountNumber.value && validateAccountNumber()) {
            debouncedResolve();
        } else {
            debouncedResolve.cancel();
        }
    });

    // Watch for changes in withdrawal amount
    watch(() => withdrawalForm.amount, () => {
        withdrawalErrorMessage.value = '';
        withdrawalSuccessMessage.value = '';
    });

    // Function to handle account form submission
    const submitAccountForm = () => {
        if (!accountName.value) {
            errorMessage.value = 'Please resolve your account details first';
            return;
        }

        accountForm.post(route('user.payments.add.account'), {
            preserveScroll: true,
            onSuccess: () => {
                selectedBankCode.value = '';
                accountNumber.value = '';
                accountName.value = '';
                resolvedBankName.value = '';
                accountForm.reset();
            },
            onError: (errors) => {
                errorMessage.value = errors.message || 'Failed to add account';
            }
        });
    };

    // Function to handle withdrawal form submission
    const submitWithdrawalForm = () => {
        if (!validateWithdrawalAmount()) {
            return;
        }

        withdrawalForm.post(route('user.payments.request.withdrawal'), {
            preserveScroll: true,
            onSuccess: () => {
                withdrawalSuccessMessage.value = 'Withdrawal request submitted successfully';
                withdrawalForm.reset();
            },
            onError: (errors) => {
                withdrawalErrorMessage.value = errors.message || 'Failed to request withdrawal';
            }
        });
    };

    const formatAccountNumber = (accountNumber: string): string => {
        if (!accountNumber || accountNumber.length < 10) {
            return 'Invalid account number';
        }
        const firstFour = accountNumber.slice(0, 4);
        const lastFour = accountNumber.slice(-4);
        return `${firstFour} **** **** ${lastFour}`;
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Withdrawals`" />

    <!-- Account payment content -->
    <div class="col-lg-9">
        <h1 class="h2">Withdrawals</h1>
        <p class="pb-2 pb-lg-3">Add your payment method to withdraw funds securely</p>

        <!-- Cards -->
        <div class="row g-4">
            <!-- Earning item -->
            <div class="col-md-6 col-lg-6">
                <div class="card card-body border p-4 h-100">
                    <h6 class="mb-0">Balance</h6>
                    <h3 class="mb-2 mt-2">₦{{ formatCurrency(props.user?.balance) }}</h3>
                    <a data-bs-toggle="modal" data-bs-target=".request-withdrawal-modal" class="mt-auto text-light-emphasis fs-base cursor-pointer">Request Withdrawal</a>
                </div>
            </div>

            <!-- Grid item -->
            <div v-if="props.withdrawal_details" class="col-lg-6">
                <div class="card w-100 border-0">
                    <div class="card-body position-relative z-2">
                        <div class="d-flex align-items-center pb-4 mb-2 mb-md-3">
                            <span class="badge text-bg-light">Default</span>
                        </div>

                        <div class="h5 pt-md-1 pb-2 pb-md-3" style="letter-spacing: 1.25px">{{ formatAccountNumber(props.withdrawal_details.account_number) }}</div>
                        <div class="d-flex justify-content-between">
                            <div class="me-3">
                                <div class="fs-xs text-body mb-1">Name</div>
                                <div class="h6 fs-sm mb-0">{{ props.withdrawal_details.account_name }}</div>
                            </div>
                            <div>
                                <div class="fs-xs text-body mb-1">Bank</div>
                                <div class="h6 fs-sm mb-0">{{ props.withdrawal_details.bank_name }}</div>
                            </div>
                        </div>
                    </div>
                    <span class="position-absolute top-0 start-0 w-100 h-100 rounded-4 d-none-dark" style="background: linear-gradient(90deg, #accbee 0%, #dbeafe 100%)"></span>
                    <span class="position-absolute top-0 start-0 w-100 h-100 rounded-4 d-none d-block-dark" style="background: linear-gradient(90deg, #1b273a 0%, #1f2632 100%)"></span>
                </div>
            </div>

            <div v-else class="col-lg-6">
                <div class="card border-0 p-4 h-100">
                    <span class="position-absolute top-0 start-0 w-100 h-100 border border-dashed border-secondary border-opacity-25 rounded d-none-dark"></span>
                    <span class="position-absolute top-0 start-0 w-100 h-100 border border-dashed border-light border-opacity-25 rounded d-none d-block-dark"></span>
                    <div class="card-body position-relative z-2 nav align-items-center justify-content-center py-5">
                        <a class="nav-link animate-underline stretched-link min-w-0 fs-base px-0 cursor-pointer" data-bs-toggle="modal" data-bs-target=".withdraw-details-modal">
                            <i class="ci-plus fs-lg ms-n2 me-2"></i>
                            <span class="animate-target text-truncate">Add payment method</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info alert -->
        <div class="alert alert-secondary mb-4 mb-sm-3 mb-md-4 mt-4" role="alert">
            <div class="fw-semibold mb-2">Upcoming payout</div>
            <p class="fs-sm mb-0">Your current earnings of <span class="fw-semibold">₦{{ formatCurrency(props.user?.balance) }}</span> will be sent to you on September 2, 2025</p>
        </div>

        <!-- Payouts table -->
        <div class="table-responsive">
            <table class="table align-middle fs-sm">
                <thead>
                    <tr>
                        <th class="ps-0" scope="col">
                            <span class="fw-normal text-body">#</span>
                        </th>

                        <th scope="col">
                            <span class="fw-normal text-body">Amount</span>
                        </th>

                        <th scope="col">
                            <span class="fw-normal text-body">Bank</span>
                        </th>

                        <th scope="col">
                            <span class="fw-normal text-body">Date</span>
                        </th>

                        <th scope="col">
                            <span class="fw-normal text-body">Status</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="payouts-list">
                    <tr v-for="(withdrawal, index) in withdrawals.data" :key="withdrawal.id">
                        <td class="fw-medium py-3 ps-0">{{ withdrawals.from + index }}</td>

                        <td class="py-3">₦{{ formatCurrency(withdrawal.amount) }}</td>
                        <td class="py-3">{{ withdrawal.withdrawal_settings.bank_name }}</td>
                        <td class="py-3">{{ new Date(withdrawal.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) }}</td>
                        <td>
                            <span class="badge fs-xs rounded-pill"
                                  :class="{
                                    'text-success bg-success-subtle': withdrawal.status === 'approved',
                                    'text-danger bg-danger-subtle': withdrawal.status === 'rejected',
                                    'text-warning bg-warning-subtle': withdrawal.status === 'pending'
                                  }">
                                {{ withdrawal.status === 'pending' ? 'Pending' :
                                withdrawal.status === 'approved' ? 'Approved' :
                                    withdrawal.status === 'rejected' ? 'Rejected' : '' }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="withdrawals.data.length === 0">
                        <td class="py-3 ps-0 pt-4 pb-4" colspan="5">
                            <h2 class="h6 pt-2 mb-2">No withdrawals yet</h2>
                            <p class="fs-sm mb-4" style="max-width: 640px">
                                You haven't made any withdrawal requests yet. When you receive donations, you can request withdrawals to your bank account here.
                            </p>

                            <a data-bs-toggle="modal" data-bs-target=".request-withdrawal-modal" class="btn btn-dark">
                                <i class="ci-plus fs-base ms-n1 me-2"></i>
                                Request Withdrawal
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination :categories="withdrawals" />
    </div>

    <!-- Add payment method modal -->
    <div class="modal fade withdraw-details-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add payment method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Alert -->
                    <div class="alert alert-secondary mb-3 text-start" role="alert">
                        <p class="small mb-0"><b>Note:</b> Withdrawal accounts cannot be modified after creation. Contact support for updates.</p>
                    </div>
                    <form @submit.prevent="submitAccountForm">
                        <div class="mb-3">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <ChoicesSelect v-model="selectedBankCode" :options="bankOptions" placeholder="Select bank" class="form-control-lg" :required="true" :class="{'is-invalid': !selectedBankCode && (accountForm.errors.bank_code || errorMessage)}"/>
                            <div v-if="!selectedBankCode && accountForm.errors.bank_code" class="invalid-feedback">
                                {{ accountForm.errors.bank_code }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="account_number">Account Number <span class="text-danger">*</span></label>
                            <input class="form-control" v-model="accountNumber" id="account_number" type="text" pattern="\d*" inputmode="numeric" autocomplete="off" placeholder="Enter Account Number" :class="{ 'is-invalid': (!accountNumber || errorMessage) && accountForm.errors.account_number }" @input="validateAccountNumber">
                            <div v-if="(!accountNumber || errorMessage) && accountForm.errors.account_number" class="invalid-feedback">
                                {{ accountForm.errors.account_number }}
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-12 mt-1">
                            <!-- Resolve Account -->
                            <div v-if="isLoading" class="spinner-grow text-primary mt-2 mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>

                            <!-- Success Alert -->
                            <div v-if="successMessage" class="alert alert-success small text-start mt-2 mb-3" role="alert">
                                {{ successMessage }}
                            </div>

                            <!-- Error Alert -->
                            <div v-if="errorMessage" class="alert alert-danger small text-start mt-2 mb-3" role="alert">
                                {{ errorMessage }}
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="reset" class="btn btn-secondary w-100" data-bs-dismiss="modal" data-bs-target=".withdraw-details-modal">Cancel</button>
                            <LoadingButton type="submit" :custom-classes="'btn btn-primary w-100'" :processing="accountForm.processing">
                                Add account
                            </LoadingButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Request withdrawal modal -->
    <div class="modal fade request-withdrawal-modal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestWithdrawalModalLabel">Request Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitWithdrawalForm">
                        <div class="mb-3">
                            <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">₦</span>
                                <input id="amount" type="number" class="form-control form-control-lg" :class="{ 'is-invalid': withdrawalErrorMessage || withdrawalForm.errors.amount }" v-model.number="withdrawalForm.amount" @focus="withdrawalForm.clearErrors('amount')" placeholder="0.00" min="0" step="0.01" />
                                <div v-if="withdrawalForm.errors.amount || withdrawalErrorMessage" class="invalid-feedback">
                                    {{ withdrawalForm.errors.amount || withdrawalErrorMessage }}
                                </div>
                            </div>
                        </div>
                        <!-- Success Alert -->
                        <div v-if="withdrawalSuccessMessage" class="alert alert-success small text-start mt-2 mb-3" role="alert">
                            {{ withdrawalSuccessMessage }}
                        </div>

                        <!-- Error Alert -->
                        <div v-if="withdrawalErrorMessage && !withdrawalForm.errors.amount" class="alert alert-danger small text-start mt-2 mb-3" role="alert">
                            {{ withdrawalErrorMessage }}
                        </div>

                        <div class="d-flex gap-3">
                            <button type="reset" class="btn btn-secondary w-100" data-bs-dismiss="modal" data-bs-target=".request-withdrawal-modal">Cancel</button>
                            <LoadingButton type="submit" :custom-classes="'btn btn-primary w-100'" :processing="withdrawalForm.processing">
                                Request Withdrawal
                            </LoadingButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
