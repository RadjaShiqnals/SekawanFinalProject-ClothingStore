import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';

export default function AdminClothes() {
    const { props } = usePage();
    const token = props.auth.token;
    const [transactions, setTransactions] = useState([]);
    const [selectedTransactionDetails, setSelectedTransactionDetails] = useState(null);
    const [showModal, setShowModal] = useState(false);

    useEffect(() => {
        fetchTransactions();
    }, []);

    const fetchTransactions = async () => {
        try {
            const response = await axios.get('http://localhost:8000/api/admin/get-transaksi', {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            setTransactions(response.data.data);
        } catch (error) {
            console.error('Error fetching transactions:', error);
        }
    };

    const handleViewDetails = async (pembelianId) => {
        try {
            const response = await axios.get(`http://localhost:8000/api/admin/pakaian-details/${pembelianId}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            setSelectedTransactionDetails(response.data.data);
            setShowModal(true);
        } catch (error) {
            console.error('Error fetching transaction details:', error);
        }
    };

    const handleDeleteTransaction = async (transactionId) => {
        if (!confirm('Are you sure you want to delete this transaction?')) return;

        try {
            await axios.delete(`http://localhost:8000/api/admin/delete-transactions/${transactionId}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            fetchTransactions(); // Refresh the transactions list
        } catch (error) {
            console.error('Error deleting transaction:', error);
        }
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedTransactionDetails(null);
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Admin Clothes
                </h2>
            }
        >
            <Head title="Admin Clothes" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3>Transactions</h3>
                            <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                <thead>
                                    <tr>
                                        <th className="py-2">Pembelian ID</th>
                                        <th className="py-2">User Name</th>
                                        <th className="py-2">Metode Pembayaran</th>
                                        <th className="py-2">Total Harga</th>
                                        <th className="py-2">Status</th>
                                        <th className="py-2">Created At</th>
                                        <th className="py-2">Updated At</th>
                                        <th className="py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.map(transaction => (
                                        <tr key={transaction.pembelian_id}>
                                            <td className="py-2">{transaction.pembelian_id}</td>
                                            <td className="py-2">{transaction.user.fullname}</td>
                                            <td className="py-2">{transaction.metode_pembayaran ? transaction.metode_pembayaran.metode_pembayaran_jenis : 'N/A'}</td>
                                            <td className="py-2">{transaction.pembelian_total_harga}</td>
                                            <td className="py-2">
                                            {transaction.status === 'lunas' ? (
                                                    <span className="text-green-500">Lunas</span>
                                                ) : (
                                                    <span className="text-red-500">Belum Bayar</span>
                                            )}
                                            </td>
                                            <td className="py-2">{new Date(transaction.created_at).toLocaleString()}</td>
                                            <td className="py-2">{new Date(transaction.updated_at).toLocaleString()}</td>
                                            <td className="py-2">
                                                <button
                                                    onClick={() => handleViewDetails(transaction.pembelian_id)}
                                                    className="mr-2 p-2 bg-blue-500 text-white rounded"
                                                >
                                                    View Details
                                                </button>
                                                <button
                                                    onClick={() => handleDeleteTransaction(transaction.pembelian_id)}
                                                    className="p-2 bg-red-500 text-white rounded"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            {showModal && (
                                <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 backdrop-blur-sm">
                                    <div className="bg-white p-6 rounded-lg shadow-lg dark:bg-gray-700 max-w-4xl w-full">
                                        <h4 className="text-lg font-semibold mb-4">Transaction Details</h4>
                                        <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                            <thead>
                                                <tr>
                                                    <th className="py-2">Pakaian ID</th>
                                                    <th className="py-2">Nama</th>
                                                    <th className="py-2">Gambar</th>
                                                    <th className="py-2">Jumlah</th>
                                                    <th className="py-2">Total Harga</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {selectedTransactionDetails.map(detail => (
                                                    <tr key={detail.pembelian_detail_id}>
                                                        <td className="py-2">{detail.pakaian.pakaian_id}</td>
                                                        <td className="py-2">{detail.pakaian.pakaian_nama}</td>
                                                        <td className="py-2">
                                                            <img src={`/storage/pakaian/${detail.pakaian.pakaian_gambar_url}`} alt={detail.pakaian.pakaian_nama} className="w-32 h-32 object-cover mx-auto" />
                                                        </td>
                                                        <td className="py-2">{detail.pembelian_detail_jumlah}</td>
                                                        <td className="py-2">{detail.pembelian_detail_total_harga}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                        <div className="flex justify-between items-center mt-4">
                                            <div className="text-lg font-semibold">
                                                Total Harga: {selectedTransactionDetails.reduce((total, detail) => total + detail.pembelian_detail_total_harga, 0)}
                                            </div>
                                            <button
                                                onClick={closeModal}
                                                className="p-2 bg-red-500 text-white rounded"
                                            >
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}