document.addEventListener('DOMContentLoaded', function () {
    const appShell = document.body;
    const sidebar = document.getElementById('appSidebar');
    const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
    const sidebarToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const mobileSidebarQuery = window.matchMedia('(max-width: 992px)');

    const setSidebarState = function (isOpen) {
        if (!sidebar) {
            return;
        }

        appShell.classList.toggle('sidebar-open', isOpen);
        sidebarToggleButtons.forEach(function (button) {
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    };

    const closeSidebar = function () {
        setSidebarState(false);
    };

    const openSidebar = function () {
        if (!mobileSidebarQuery.matches) {
            return;
        }

        setSidebarState(true);
    };

    sidebarToggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!mobileSidebarQuery.matches) {
                return;
            }

            setSidebarState(!appShell.classList.contains('sidebar-open'));
        });
    });

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    if (sidebar) {
        sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (mobileSidebarQuery.matches) {
                    closeSidebar();
                }
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && appShell.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    const syncSidebarOnResize = function () {
        if (!mobileSidebarQuery.matches) {
            closeSidebar();
        }
    };

    if (typeof mobileSidebarQuery.addEventListener === 'function') {
        mobileSidebarQuery.addEventListener('change', syncSidebarOnResize);
    } else if (typeof mobileSidebarQuery.addListener === 'function') {
        mobileSidebarQuery.addListener(syncSidebarOnResize);
    }

    const tableSelector = '.js-datatable, .datatable';
    const dataTablesRegistry = new Map();

    const prepareResponsiveTable = function (table) {
        if (table.dataset.mobilePrepared === 'true') {
            return;
        }

        const headerRow = table.querySelector('thead tr');
        const bodyRows = table.querySelectorAll('tbody tr');

        if (!headerRow || bodyRows.length === 0) {
            return;
        }

        const originalHeaderCount = headerRow.children.length;
        const hasColspanRow = Array.from(bodyRows).some(function (row) {
            return Array.from(row.children).some(function (cell) {
                return cell.colSpan > 1 || row.children.length !== originalHeaderCount;
            });
        });

        if (hasColspanRow) {
            table.dataset.mobilePrepared = 'true';
            return;
        }

        const hiddenColumns = Array.from(headerRow.children)
            .map(function (cell, index) {
                return cell.dataset.mobileHidden === 'true' ? index : null;
            })
            .filter(function (index) {
                return index !== null;
            });

        table.dataset.mobilePrepared = 'true';

        if (hiddenColumns.length === 0) {
            return;
        }

        const detailHeader = document.createElement('th');
        detailHeader.textContent = 'Détail';
        detailHeader.className = 'table-detail-control-column';
        headerRow.insertBefore(detailHeader, headerRow.firstElementChild);

        bodyRows.forEach(function (row) {
            const detailCell = document.createElement('td');
            detailCell.className = 'table-detail-control-cell';
            detailCell.innerHTML = '<button type="button" class="btn btn-sm btn-outline-secondary table-detail-toggle" aria-expanded="false">Détail</button>';
            row.insertBefore(detailCell, row.firstElementChild);
        });

        table.dataset.mobileHiddenColumns = hiddenColumns.join(',');
    };

    const escapeHtml = function (value) {
        const container = document.createElement('div');
        container.textContent = value;
        return container.innerHTML;
    };

    const buildDetailHtml = function (api, table) {
        const hiddenColumns = (table.dataset.mobileHiddenColumns || '')
            .split(',')
            .filter(Boolean)
            .map(function (index) {
                return Number(index) + 1;
            });

        return function (row) {
            const rowData = row.data();
            const headers = Array.from(table.querySelectorAll('thead th'));
            const items = hiddenColumns
                .map(function (index) {
                    const label = headers[index] ? headers[index].textContent.trim() : '';
                    const value = rowData[index];

                    if (!label || value === null || value === undefined || value === '') {
                        return '';
                    }

                    return '<div class="table-detail-item"><span class="table-detail-label">' + escapeHtml(label) + '</span><div class="table-detail-value">' + value + '</div></div>';
                })
                .filter(Boolean)
                .join('');

            return items === '' ? '<div class="table-detail-empty">Aucun détail supplémentaire.</div>' : '<div class="table-detail-panel">' + items + '</div>';
        };
    };

    if (window.jQuery && document.querySelector(tableSelector)) {
        document.querySelectorAll(tableSelector).forEach(function (table) {
            prepareResponsiveTable(table);

            const hiddenColumns = (table.dataset.mobileHiddenColumns || '')
                .split(',')
                .filter(Boolean)
                .map(function (index) {
                    return Number(index) + 1;
                });

            const options = {
                pageLength: 10,
                lengthChange: false,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
                }
            };

            if (hiddenColumns.length > 0) {
                options.columnDefs = [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        className: 'table-detail-control-column'
                    }
                ];
            }

            const dataTable = jQuery(table).DataTable(options);
            if (table.id) {
                dataTablesRegistry.set('#' + table.id, dataTable);
            }
            const renderDetails = buildDetailHtml(dataTable, table);

            const applyResponsiveState = function () {
                const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

                if (hiddenColumns.length === 0) {
                    return;
                }

                dataTable.column(0).visible(isMobile, false);
                hiddenColumns.forEach(function (index) {
                    dataTable.column(index).visible(!isMobile, false);
                });

                if (!isMobile) {
                    dataTable.rows().every(function () {
                        this.child.hide();
                    });

                    table.querySelectorAll('.table-detail-toggle').forEach(function (button) {
                        button.setAttribute('aria-expanded', 'false');
                        button.textContent = 'Détail';
                    });
                }

                dataTable.columns.adjust().draw(false);
            };

            applyResponsiveState();
            window.addEventListener('resize', applyResponsiveState);

            table.addEventListener('click', function (event) {
                const button = event.target.closest('.table-detail-toggle');

                if (!button) {
                    return;
                }

                const tr = button.closest('tr');
                const row = dataTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.classList.remove('table-row-details-open');
                    button.setAttribute('aria-expanded', 'false');
                    button.textContent = 'Détail';
                    return;
                }

                row.child(renderDetails(row)).show();
                tr.classList.add('table-row-details-open');
                button.setAttribute('aria-expanded', 'true');
                button.textContent = 'Masquer';
            });
        });

        document.querySelectorAll('[data-datatable-target]').forEach(function (input) {
            const targetSelector = input.dataset.datatableTarget || '';
            const dataTable = dataTablesRegistry.get(targetSelector);

            if (!dataTable) {
                return;
            }

            input.addEventListener('input', function () {
                dataTable.search(input.value).draw();
            });
        });
    }

    const salesChart = document.getElementById('salesChart');
    if (salesChart && window.Chart) {
        const labels = JSON.parse(salesChart.dataset.labels || '[]');
        const values = JSON.parse(salesChart.dataset.values || '[]');

        new Chart(salesChart, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Factures validées',
                    data: values,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.15)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    }

    const cashierPaymentModal = document.getElementById('cashierPaymentModal');
    if (cashierPaymentModal) {
        const paymentForm = document.getElementById('cashierPaymentForm');
        const paymentModalInstance = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(cashierPaymentModal) : null;
        const paymentError = document.getElementById('cashierPaymentError');
        const paymentSubmit = document.getElementById('cashierPaymentSubmit');
        let lastPaymentTrigger = null;

        const populateCashierPaymentModal = function (trigger) {
            if (!trigger) {
                return;
            }

            const invoiceIdInput = document.getElementById('cashierPaymentInvoiceId');
            const invoiceNumber = document.getElementById('cashierPaymentInvoiceNumber');
            const clientName = document.getElementById('cashierPaymentClientName');
            const balanceLabel = document.getElementById('cashierPaymentBalanceLabel');
            const amountInput = document.getElementById('cashier_payment_amount');

            const invoiceId = trigger.dataset.invoiceId || trigger.getAttribute('data-invoice-id') || '';
            const invoiceNumberValue = trigger.dataset.invoiceNumber || trigger.getAttribute('data-invoice-number') || '—';
            const clientNameValue = trigger.dataset.clientName || trigger.getAttribute('data-client-name') || '—';
            const balanceDue = trigger.dataset.balanceDue || trigger.getAttribute('data-balance-due') || '';
            const balanceText = trigger.dataset.balanceLabel || trigger.getAttribute('data-balance-label') || '0,00';

            if (invoiceIdInput) {
                invoiceIdInput.value = invoiceId;
            }

            if (invoiceNumber) {
                invoiceNumber.textContent = invoiceNumberValue;
            }

            if (clientName) {
                clientName.textContent = clientNameValue;
            }

            if (balanceLabel) {
                balanceLabel.textContent = balanceText;
            }

            if (amountInput) {
                amountInput.value = balanceDue;
                amountInput.max = balanceDue;
            }

            if (paymentError) {
                paymentError.classList.add('d-none');
                paymentError.textContent = '';
            }
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.js-open-payment-modal');

            if (!trigger) {
                return;
            }

            lastPaymentTrigger = trigger;
            populateCashierPaymentModal(trigger);
        });

        cashierPaymentModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget || lastPaymentTrigger;
            populateCashierPaymentModal(trigger);
        });

        if (paymentForm) {
            paymentForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(paymentForm);
                const invoiceId = formData.get('invoice_id');

                if (paymentError) {
                    paymentError.classList.add('d-none');
                    paymentError.textContent = '';
                }

                if (paymentSubmit) {
                    paymentSubmit.disabled = true;
                    paymentSubmit.textContent = 'Enregistrement...';
                }

                fetch(paymentForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        return response.json().catch(function () {
                            return {
                                success: false,
                                message: 'Réponse inattendue du serveur.'
                            };
                        }).then(function (payload) {
                            return {
                                ok: response.ok,
                                payload: payload
                            };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.payload.success) {
                            throw new Error(result.payload.message || 'Paiement impossible.');
                        }

                        const invoice = result.payload.invoice || {};
                        const row = document.querySelector('[data-invoice-row-id="' + String(invoiceId) + '"]');

                        if (row) {
                            const statusBadge = row.querySelector('[data-role="invoice-status-badge"]');
                            const paidLabel = row.querySelector('[data-role="invoice-paid-label"]');
                            const balanceLabel = row.querySelector('[data-role="invoice-balance-label"]');
                            const actions = row.querySelector('[data-role="invoice-actions"]');
                            const paymentButton = row.querySelector('.js-open-payment-modal');

                            if (statusBadge) {
                                statusBadge.className = 'badge ' + (invoice.status_class || 'text-bg-secondary');
                                statusBadge.textContent = invoice.status_label || '';
                            }

                            if (paidLabel) {
                                paidLabel.textContent = 'Payé : ' + (invoice.amount_paid_label || '0,00');
                            }

                            if (balanceLabel) {
                                balanceLabel.textContent = 'Solde : ' + (invoice.balance_due_label || '0,00');
                            }

                            if (paymentButton) {
                                if ((invoice.balance_due || 0) > 0 && ['validated', 'partial_paid'].indexOf(invoice.status || '') !== -1) {
                                    paymentButton.dataset.balanceDue = String(invoice.balance_due || 0);
                                    paymentButton.dataset.balanceLabel = invoice.balance_due_label || '0,00';
                                } else {
                                    paymentButton.remove();
                                }
                            }

                            if (!paymentButton && actions && (invoice.balance_due || 0) <= 0) {
                                actions.classList.add('justify-content-end');
                            }
                        }

                        paymentForm.reset();
                        const paymentDate = document.getElementById('cashier_payment_date');
                        if (paymentDate) {
                            paymentDate.value = new Date().toISOString().slice(0, 10);
                        }

                        if (paymentModalInstance) {
                            paymentModalInstance.hide();
                        }

                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'success',
                                title: 'Paiement enregistré',
                                text: result.payload.message || 'Le paiement a été enregistré avec succès.',
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    })
                    .catch(function (error) {
                        if (paymentError) {
                            paymentError.textContent = error.message || 'Paiement impossible.';
                            paymentError.classList.remove('d-none');
                        }
                    })
                    .finally(function () {
                        if (paymentSubmit) {
                            paymentSubmit.disabled = false;
                            paymentSubmit.textContent = 'Enregistrer le paiement';
                        }
                    });
            });
        }
    }
});
