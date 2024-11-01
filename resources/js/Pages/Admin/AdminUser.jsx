import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';

export default function AdminDashboard() {
    const { props } = usePage();
    const token = props.auth.token;
    const [users, setUsers] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [modalType, setModalType] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [formData, setFormData] = useState({
        username: '',
        password: '',
        password_confirmation: '',
        fullname: '',
        email: '',
        phonenumber: '',
        alamat: '',
        profilepicture: null,
        role: 'Pengguna',
    });
    const [errors, setErrors] = useState({});
    const [successMessage, setSuccessMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    useEffect(() => {
        // Fetch users data
        axios.get('/api/admin/get-user', {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })
        .then(response => {
            setUsers(response.data.data);
        })
        .catch(error => {
            console.error('Error fetching users:', error);
        });
    }, [token]);

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleFileChange = (e) => {
        setFormData({ ...formData, profilepicture: e.target.files[0] });
    };

    const handleCreateUser = () => {
        setModalType('create');
        setFormData({
            username: '',
            password: '',
            password_confirmation: '',
            fullname: '',
            email: '',
            phonenumber: '',
            alamat: '',
            profilepicture: null,
            role: 'Pengguna',
        });
        setErrors({});
        setShowModal(true);
    };

    const handleEditUser = (user) => {
        setModalType('edit');
        setSelectedUser(user);
        setFormData({
            username: user.username,
            password: '',
            password_confirmation: '',
            fullname: user.fullname,
            email: user.email,
            phonenumber: user.phonenumber,
            alamat: user.alamat,
            profilepicture: null,
            role: user.role,
        });
        setErrors({});
        setShowModal(true);
    };

    const handleDeleteUser = (userId) => {
        if (confirm('Are you sure you want to delete this user?')) {
            axios.delete(`/api/admin/delete-user/${userId}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            .then(response => {
                setSuccessMessage('User deleted successfully');
                setUsers(users.filter(user => user.id !== userId));
            })
            .catch(error => {
                setErrorMessage('Error deleting user');
                console.error('Error deleting user:', error);
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const formDataObj = new FormData();
        Object.keys(formData).forEach(key => {
            if (formData[key] || (key === 'profilepicture' && formData[key] !== null)) {
                formDataObj.append(key, formData[key]);
            }
        });

        const url = modalType === 'create' ? '/api/admin/create-user' : `/api/admin/update-user/${selectedUser.id}`;
        axios.post(url, formDataObj, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            setSuccessMessage(`User ${modalType === 'create' ? 'created' : 'updated'} successfully`);
            setShowModal(false);
            setFormData({
                username: '',
                password: '',
                password_confirmation: '',
                fullname: '',
                email: '',
                phonenumber: '',
                alamat: '',
                profilepicture: null,
                role: 'Pengguna',
            });
            setUsers(modalType === 'create' ? [...users, response.data.data] : users.map(user => user.id === selectedUser.id ? response.data.data : user));
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                setErrors(error.response.data || {});
                console.log('Validation errors:', error.response.data);
                setShowModal(false);
            } else {
                setErrorMessage(`Error ${modalType === 'create' ? 'creating' : 'updating'} user`);
                setShowModal(false);
            }
        });
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedUser(null);
        setFormData({
            username: '',
            password: '',
            password_confirmation: '',
            fullname: '',
            email: '',
            phonenumber: '',
            alamat: '',
            profilepicture: null,
            role: 'Pengguna',
        });
        setErrors({});
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Admin Dashboard
                </h2>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3>User List</h3>
                            {successMessage && <p className="text-green-500 mb-4">{successMessage}</p>}
                            {errorMessage && <p className="text-red-500 mb-4">{errorMessage}</p>}
                            <button
                                onClick={handleCreateUser}
                                className="mb-4 p-2 bg-green-500 text-white rounded"
                            >
                                Create User
                            </button>
                            <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                <thead>
                                    <tr>
                                        <th className="py-2">ID</th>
                                        <th className="py-2">Username</th>
                                        <th className="py-2">Full Name</th>
                                        <th className="py-2">Email</th>
                                        <th className="py-2">Phone Number</th>
                                        <th className="py-2">Address</th>
                                        <th className="py-2">Role</th>
                                        <th className="py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.map(user => (
                                        <tr key={user.id}>
                                            <td className="py-2">{user.id}</td>
                                            <td className="py-2">{user.username}</td>
                                            <td className="py-2">{user.fullname}</td>
                                            <td className="py-2">{user.email}</td>
                                            <td className="py-2">{user.phonenumber}</td>
                                            <td className="py-2">{user.alamat}</td>
                                            <td className="py-2">{user.role}</td>
                                            <td className="py-2">
                                                <button
                                                    onClick={() => handleEditUser(user)}
                                                    className="mr-2 p-2 bg-blue-500 text-white rounded"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    onClick={() => handleDeleteUser(user.id)}
                                                    className="p-2 bg-red-500 text-white rounded"
                                                >
                                                    Delete
                                                </button>
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
                        <h3 className="text-lg font-semibold mb-4">{modalType === 'create' ? 'Create User' : 'Edit User'}</h3>
                        <form onSubmit={handleSubmit}>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                <input
                                    type="text"
                                    name="username"
                                    value={formData.username}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                />
                                {errors.username && errors.username.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    value={formData.password}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required={modalType === 'create'}
                                />
                                {errors.password && errors.password.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    value={formData.password_confirmation}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required={modalType === 'create'}
                                />
                                {errors.password_confirmation && errors.password_confirmation.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                <input
                                    type="text"
                                    name="fullname"
                                    value={formData.fullname}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                />
                                {errors.fullname && errors.fullname.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                />
                                {errors.email && errors.email.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                                <input
                                    type="text"
                                    name="phonenumber"
                                    value={formData.phonenumber}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                />
                                {errors.phonenumber && errors.phonenumber.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                <input
                                    type="text"
                                    name="alamat"
                                    value={formData.alamat}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                />
                                {errors.alamat && errors.alamat.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Picture</label>
                                <input
                                    type="file"
                                    name="profilepicture"
                                    onChange={handleFileChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                />
                                {errors.profilepicture && errors.profilepicture.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select
                                    name="role"
                                    value={formData.role}
                                    onChange={handleInputChange}
                                    className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                    required
                                >
                                    <option value="Pengguna">Pengguna</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                {errors.role && errors.role.map((error, index) => (
                                    <p key={index} className="text-red-500 text-sm mt-1">{error}</p>
                                ))}
                            </div>
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    className="mr-2 p-2 bg-blue-500 text-white rounded"
                                >
                                    {modalType === 'create' ? 'Create' : 'Update'}
                                </button>
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="p-2 bg-red-500 text-white rounded"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}