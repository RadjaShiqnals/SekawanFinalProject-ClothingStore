import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';

export default function UserDashboard() {
    const { props } = usePage();
    const token = props.auth.token;
    const [clothes, setClothes] = useState([]);
    const [selectedItems, setSelectedItems] = useState({});

    useEffect(() => {
        // Fetch clothes data
        axios.get('/api/user/get-pakaian', {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            setClothes(response.data.data);
        })
        .catch(error => {
            console.error('Error fetching clothes:', error);
        });
    }, [token]);

    const handleQuantityChange = (pakaianId, quantity) => {
        setSelectedItems(prevItems => ({
            ...prevItems,
            [pakaianId]: quantity
        }));
    };

    const handleSubmit = () => {
        const items = Object.entries(selectedItems)
            .filter(([pakaian_id, quantity]) => quantity && quantity !== '0')
            .map(([pakaian_id, quantity]) => ({
                pakaian_id: parseInt(pakaian_id),
                quantity: parseInt(quantity)
            }));
    
        axios.post('/api/user/add-new-item', { items }, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            alert('Items added successfully');
            window.location.href = route('user.transaksi');
        })
        .catch(error => {
            console.error('Error adding items:', error);
        });
    };

    const calculateTotalPrice = () => {
        return Object.entries(selectedItems).reduce((total, [pakaian_id, quantity]) => {
            const cloth = clothes.find(c => c.pakaian_id === parseInt(pakaian_id));
            return total + (cloth ? cloth.pakaian_harga * quantity : 0);
        }, 0);
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    User Dashboard
                </h2>
            }
        >
            <Head title="User Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3>Available Clothes</h3>
                            <div className="flex flex-wrap -mx-2">
                                {clothes.map(cloth => (
                                    <div key={cloth.pakaian_id} className="w-full sm:w-1/2 md:w-1/3 px-2 mb-4">
                                        <div className="bg-white p-4 rounded-lg shadow-md dark:bg-gray-700">
                                            <img src={`/storage/pakaian/${cloth.pakaian_gambar_url}`} alt={cloth.pakaian_nama} className="w-full h-32 object-cover mb-2"/>
                                            <h4 className="text-lg font-semibold">{cloth.pakaian_nama}</h4>
                                            <p>Price: {cloth.pakaian_harga.toLocaleString('id-ID', {style: 'currency', currency: 'IDR'})}</p>
                                            <p>Stock: {cloth.pakaian_stok}</p>
                                            <p>Category: {cloth.kategori_pakaian.kategori_pakaian_nama}</p>
                                            <input
                                                type="text"
                                                min="0"
                                                max="99"
                                                maxLength="2"
                                                value={selectedItems[cloth.pakaian_id] || 0}
                                                onChange={(e) => {
                                                    const value = e.target.value;
                                                    if (/^\d*$/.test(value)) {
                                                        handleQuantityChange(cloth.pakaian_id, value);
                                                    }
                                                }}
                                                pattern="\d*"
                                                className="mt-2 p-2 border rounded w-full dark:text-gray-50"
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="flex justify-between items-center mt-4">
                                <button onClick={handleSubmit} className="p-2 bg-blue-500 text-white rounded">
                                    Add Items to Cart
                                </button>
                                <div className="text-lg font-semibold">
                                    Total Price: {calculateTotalPrice().toLocaleString('id-ID', {style: 'currency', currency: 'IDR'})}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}