import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';
import moment from 'moment-timezone';
import 'moment/locale/id';

export default function UserTransactions() {
    const { props } = usePage();
    const token = props.auth.token;
    const [transactions, setTransactions] = useState([]);
    const [selectedTransaction, setSelectedTransaction] = useState(null);
    const [transactionDetails, setTransactionDetails] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [showPaymentModal, setShowPaymentModal] = useState(false);
    const [paymentMethods, setPaymentMethods] = useState([]);
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState(null);

    useEffect(() => {
        // Fetch transaction data
        axios.get('/api/user/get-transaksi', {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            setTransactions(response.data.data);
        })
        .catch(error => {
            console.error('Error fetching transactions:', error);
        });
    }, [token]);

    const handleViewDetails = (transactionId) => {
        axios.get(`/api/user/get-detail-transaksi/${transactionId}`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            setTransactionDetails(response.data.data);
            setSelectedTransaction(transactionId);
            setShowModal(true);
        })
        .catch(error => {
            console.error('Error fetching transaction details:', error);
        });
    };

    const handlePay = (transactionId) => {
        setSelectedTransaction(transactionId);
        axios.get('/api/user/get-metode-pembayaran', {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            setPaymentMethods(response.data.data);
            setShowPaymentModal(true);
        })
        .catch(error => {
            console.error('Error fetching payment methods:', error);
        });
    };

    const handlePaymentMethodSelect = (paymentMethodId) => {
        setSelectedPaymentMethod(paymentMethodId);
    };

    const handleConfirmPayment = () => {
        if (!selectedPaymentMethod) {
            alert('Please select a payment method');
            return;
        }
    
        axios.post('/api/user/pay-cart', {
            pembelian_id: selectedTransaction,
            metode_pembayaran_id: selectedPaymentMethod
        }, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            const updatedTransactions = transactions.map(transaction => {
                if (transaction.pembelian_id === selectedTransaction) {
                    return {
                        ...transaction,
                        status: 'lunas',
                        metode_pembayaran: {
                            metode_pembayaran_id: selectedPaymentMethod,
                            metode_pembayaran_jenis: paymentMethods.find(method => method.metode_pembayaran_id === selectedPaymentMethod).metode_pembayaran_jenis
                        }
                    };
                }
                return transaction;
            });
    
            setTransactions(updatedTransactions);
            alert('Payment successful');
            setShowPaymentModal(false);
            setSelectedPaymentMethod(null);
        })
        .catch(error => {
            console.error('Error processing payment:', error);
        });
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedTransaction(null);
        setTransactionDetails([]);
    };

    const closePaymentModal = () => {
        setShowPaymentModal(false);
        setSelectedPaymentMethod(null);
    };

    const formatDate = (dateString) => {
        return moment(dateString).tz('Asia/Jakarta').locale('id').format('DD MMMM YYYY HH:mm:ss');
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    User Transactions
                </h2>
            }
        >
            <Head title="User Transactions" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3>Transaction List</h3>
                            <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                <thead>
                                    <tr>
                                        <th className="py-2">Transaction ID</th>
                                        <th className="py-2">Payment Method</th>
                                        <th className="py-2">Date</th>
                                        <th className="py-2">Total Price</th>
                                        <th className="py-2">Status</th>
                                        <th className="py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.map(transaction => (
                                        <tr key={transaction.pembelian_id}>
                                            <td className="py-2">{transaction.pembelian_id}</td>
                                            <td className="py-2">{transaction.metode_pembayaran ? transaction.metode_pembayaran.metode_pembayaran_jenis : 'N/A'}</td>
                                            <td className="py-2">{formatDate(transaction.pembelian_tanggal)}</td>
                                            <td className="py-2">{transaction.pembelian_total_harga.toLocaleString('id-ID', {style: 'currency', currency: 'IDR'})}</td>
                                            <td className="py-2">
                                                {transaction.status === 'lunas' ? (
                                                    <span className="text-green-500">Lunas</span>
                                                ) : (
                                                    <span className="text-red-500">Belum Bayar</span>
                                                )}
                                            </td>
                                            <td className="py-2">
                                                <button
                                                    onClick={() => handleViewDetails(transaction.pembelian_id)}
                                                    className="mr-2 p-2 bg-blue-500 text-white rounded"
                                                >
                                                    View Details
                                                </button>
                                                {transaction.status === 'belum_bayar' && (
                                                    <button
                                                        onClick={() => handlePay(transaction.pembelian_id)}
                                                        className="p-2 bg-green-500 text-white rounded"
                                                    >
                                                        Pay
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 backdrop-blur-sm">
                    <div className="bg-white p-6 rounded-lg shadow-lg dark:bg-gray-700 max-w-4xl w-full">
                        <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Transaction Details</h3>
                        <div className="flex flex-wrap -mx-2 text-gray-900 dark:text-gray-100">
                            {transactionDetails.map(detail => (
                                <div key={detail.pembelian_detail_id} className="w-full sm:w-1/2 md:w-1/3 px-2 mb-4">
                                    <div className="bg-white p-4 rounded-lg shadow-md dark:bg-gray-800">
                                        <p>Item: {detail.pakaian.pakaian_nama}</p>
                                        <p>Quantity: {detail.pembelian_detail_jumlah}</p>
                                        <p>Harga: {detail.pakaian.pakaian_harga.toLocaleString('id-ID', {style: 'currency', currency: 'IDR'})}</p>
                                        <p>Total Price: {detail.pembelian_detail_total_harga.toLocaleString('id-ID', {style: 'currency', currency: 'IDR'})}</p>
                                        <img src={`/storage/pakaian/${detail.pakaian.pakaian_gambar_url}`} alt={detail.pakaian.pakaian_nama} className="w-full h-32 object-cover mb-2"/>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="w-full px-2 mb-4">
                            <div className="p-4 text-right text-gray-900 dark:text-gray-100">
                                <p className="font-semibold">Total Harga:</p>
                                <p className="text-lg">
                                    {transactionDetails.reduce((total, detail) => total + detail.pembelian_detail_total_harga, 0).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={closeModal}
                            className="mt-4 p-2 bg-red-500 text-white rounded"
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}

            {showPaymentModal && (
                <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 backdrop-blur-sm">
                    <div className="bg-white p-6 rounded-lg shadow-lg dark:bg-gray-700 max-w-4xl w-full">
                        <h3 className="text-lg font-semibold mb-4">Select Payment Method</h3>
                        <div className="flex flex-wrap -mx-2">
                            {paymentMethods.map(method => (
                                <div key={method.metode_pembayaran_id} className="w-full sm:w-1/2 md:w-1/3 px-2 mb-4">
                                    <div
                                        className={`bg-white p-4 rounded-lg shadow-md dark:bg-gray-800 cursor-pointer ${selectedPaymentMethod === method.metode_pembayaran_id ? 'border-2 border-blue-500' : ''}`}
                                        onClick={() => handlePaymentMethodSelect(method.metode_pembayaran_id)}
                                    >
                                        <p>Method: {method.metode_pembayaran_jenis}</p>
                                        <p>Number: {method.metode_pembayaran_nomor}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <button
                            onClick={handleConfirmPayment}
                            className="mt-4 p-2 bg-green-500 text-white rounded"
                        >
                            Confirm Payment
                        </button>
                        <button
                            onClick={closePaymentModal}
                            className="mt-4 p-2 bg-red-500 text-white rounded"
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}