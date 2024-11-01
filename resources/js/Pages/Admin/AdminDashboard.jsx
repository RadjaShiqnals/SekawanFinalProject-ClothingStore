import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, usePage } from "@inertiajs/react";
import axios from "axios";

export default function AdminDashboard() {
    const { props } = usePage();
    const token = props.auth.token;
    const [activeTab, setActiveTab] = useState("pakaian");
    const [pakaian, setPakaian] = useState([]);
    const [kategoriPakaian, setKategoriPakaian] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [modalType, setModalType] = useState("");
    const [selectedItem, setSelectedItem] = useState(null);
    const [formData, setFormData] = useState({
        pakaian_kategori_pakaian_id: "",
        pakaian_nama: "",
        pakaian_harga: "",
        pakaian_stok: "",
        pakaian_gambar_url: null,
        kategori_pakaian_nama: "",
    });
    const [errors, setErrors] = useState({});
    const [successMessage, setSuccessMessage] = useState("");
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        fetchPakaian();
        fetchKategoriPakaian();
    }, [token]);

    const fetchPakaian = () => {
        axios
            .get("/api/admin/get-pakaian", {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then((response) => {
                setPakaian(response.data.data);
            })
            .catch((error) => {
                console.error("Error fetching pakaian:", error);
            });
    };

    const fetchKategoriPakaian = () => {
        axios
            .get("/api/admin/get-category", {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then((response) => {
                setKategoriPakaian(response.data.data);
            })
            .catch((error) => {
                console.error("Error fetching kategori pakaian:", error);
            });
    };
    const handleTabChange = (tab) => {
        console.log(`ActiveTab changed to: ${tab}`);
        setActiveTab(tab);
    };
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleFileChange = (e) => {
        setFormData({ ...formData, pakaian_gambar_url: e.target.files[0] });
    };

    const handleCreateItem = (type) => {
        setModalType(`create-${type}`);
        if (type === "pakaian") {
            setFormData({
                pakaian_kategori_pakaian_id: "",
                pakaian_nama: "",
                pakaian_harga: "",
                pakaian_stok: "",
                pakaian_gambar_url: null,
                kategori_pakaian_nama: "",
            });
        } else {
            setFormData({
                kategori_pakaian_nama: "",
            });
        }
        setErrors({});
        setShowModal(true);
    };

    const handleEditItem = (type, item) => {
        setModalType(`edit-${type}`);
        setSelectedItem(item);
        if (type === "pakaian") {
            setFormData({
                pakaian_kategori_pakaian_id: item.pakaian_kategori_pakaian_id,
                pakaian_nama: item.pakaian_nama,
                pakaian_harga: item.pakaian_harga,
                pakaian_stok: item.pakaian_stok,
                pakaian_gambar_url: null,
                kategori_pakaian_nama: "",
            });
        } else {
            setFormData({
                kategori_pakaian_nama: item.kategori_pakaian_nama,
            });
        }
        setErrors({});
        setShowModal(true);
    };

    const handleDeleteItem = (type, id) => {
        if (confirm("Are you sure you want to delete this item?")) {
            const url =
                type === "pakaian"
                    ? `/api/admin/delete-pakaian/${id}`
                    : `/api/admin/delete-kategori-pakaian/${id}`;
            axios
                .delete(url, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                })
                .then((response) => {
                    setSuccessMessage(
                        `${
                            type.charAt(0).toUpperCase() + type.slice(1)
                        } deleted successfully`
                    );
                    if (type === "pakaian") {
                        setPakaian(
                            pakaian.filter((item) => item.pakaian_id !== id)
                        );
                    } else {
                        setKategoriPakaian(
                            kategoriPakaian.filter(
                                (item) => item.kategori_pakaian_id !== id
                            )
                        );
                    }
                })
                .catch((error) => {
                    setErrorMessage(`Error deleting ${type}`);
                    console.error(`Error deleting ${type}:`, error);
                });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
    
        const formDataObj = new FormData();
        Object.keys(formData).forEach((key) => {
            if (
                formData[key] ||
                (key === "pakaian_gambar_url" && formData[key] !== null)
            ) {
                formDataObj.append(key, formData[key]);
            }
        });
    
        const url = modalType.includes("create")
            ? `/api/admin/create-${modalType.split("-")[1] === "kategori" ? "kategori-pakaian" : modalType.split("-")[1]}`
            : `/api/admin/update-${modalType.split("-")[1] === "kategori" ? "kategori-pakaian" : modalType.split("-")[1]}/${
                selectedItem.pakaian_id || selectedItem.kategori_pakaian_id
        }`;
    
        axios
            .post(url, formDataObj, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                setSuccessMessage(
                    `${
                        modalType.includes("create") ? "Created" : "Updated"
                    } successfully`
                );
                setShowModal(false);
                setFormData({
                    pakaian_kategori_pakaian_id: "",
                    pakaian_nama: "",
                    pakaian_harga: "",
                    pakaian_stok: "",
                    pakaian_gambar_url: null,
                    kategori_pakaian_nama: "",
                });
                if (/^create-pakaian$|^edit-pakaian$|^delete-pakaian$/.test(modalType)) {
                    fetchPakaian();
                } else if (/^create-kategori-pakaian$|^edit-kategori-pakaian$|^delete-kategori-pakaian$/.test(modalType)) {
                    fetchKategoriPakaian();
                } else {
                    console.error("Unknown modal type:", modalType);
                }
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    setErrors(error.response.data || {});
                } else {
                    setErrorMessage(
                        `Error ${
                            modalType.includes("create")
                                ? "creating"
                                : "updating"
                        } item`
                    );
                }
                setShowModal(false);
            });
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedItem(null);
        setFormData({
            pakaian_kategori_pakaian_id: "",
            pakaian_nama: "",
            pakaian_harga: "",
            pakaian_stok: "",
            pakaian_gambar_url: null,
            kategori_pakaian_nama: "",
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
                            <div className="mb-4">
                                <button
                                    onClick={() => handleTabChange("pakaian")}
                                    className={`mr-4 p-2 ${
                                        activeTab === "pakaian"
                                            ? "bg-blue-500"
                                            : "bg-gray-500"
                                    } text-white rounded`}
                                >
                                    Pakaian
                                </button>
                                <button
                                    onClick={() =>
                                        handleTabChange("kategori-pakaian")
                                    }
                                    className={`p-2 ${
                                        activeTab === "kategori-pakaian"
                                            ? "bg-blue-500"
                                            : "bg-gray-500"
                                    } text-white rounded`}
                                >
                                    Kategori Pakaian
                                </button>
                            </div>

                            {successMessage && (
                                <p className="text-green-500 mb-4">
                                    {successMessage}
                                </p>
                            )}
                            {errorMessage && (
                                <p className="text-red-500 mb-4">
                                    {errorMessage}
                                </p>
                            )}

{activeTab === 'pakaian' && (
                            <div>
                                <button
                                    onClick={() => handleCreateItem('pakaian')}
                                    className="mb-4 p-2 bg-green-500 text-white rounded"
                                >
                                    Create Pakaian
                                </button>
                                <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                    <thead>
                                        <tr>
                                            <th className="py-2">ID</th>
                                            <th className="py-2">Kategori</th>
                                            <th className="py-2">Nama</th>
                                            <th className="py-2">Gambar</th>
                                            <th className="py-2">Harga</th>
                                            <th className="py-2">Stok</th>
                                            <th className="py-2">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {pakaian.map(item => (
                                            <tr key={item.pakaian_id}>
                                                <td className="py-2">{item.pakaian_id}</td>
                                                <td className="py-2">{item.kategori_pakaian.kategori_pakaian_nama}</td>
                                                <td className="py-2">{item.pakaian_nama}</td>
                                                <td className="py-2">
                                                    <img src={`/storage/pakaian/${item.pakaian_gambar_url}`} alt={item.pakaian_nama} className="w-32 h-32 object-cover mx-auto" />
                                                </td>
                                                <td className="py-2">{item.pakaian_harga}</td>
                                                <td className="py-2">{item.pakaian_stok}</td>
                                                <td className="py-2">
                                                    <button
                                                        onClick={() => handleEditItem('pakaian', item)}
                                                        className="mr-2 p-2 bg-blue-500 text-white rounded"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => handleDeleteItem('pakaian', item.pakaian_id)}
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
                        )}

                        {activeTab === 'kategori-pakaian' && (
                            <div>
                                <button
                                    onClick={() => handleCreateItem('kategori-pakaian')}
                                    className="mb-4 p-2 bg-green-500 text-white rounded"
                                >
                                    Create Kategori Pakaian
                                </button>
                                <table className="min-w-full bg-white dark:bg-gray-800 text-center">
                                    <thead>
                                        <tr>
                                            <th className="py-2">ID</th>
                                            <th className="py-2">Nama</th>
                                            <th className="py-2">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {kategoriPakaian.map(item => (
                                            <tr key={item.kategori_pakaian_id}>
                                                <td className="py-2">{item.kategori_pakaian_id}</td>
                                                <td className="py-2">{item.kategori_pakaian_nama}</td>
                                                <td className="py-2">
                                                    <button
                                                        onClick={() => handleEditItem('kategori-pakaian', item)}
                                                        className="mr-2 p-2 bg-blue-500 text-white rounded"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => handleDeleteItem('kategori-pakaian', item.kategori_pakaian_id)}
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
                        )}
                    </div>
                </div>
            </div>
        </div>

        {showModal && (
    <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 backdrop-blur-sm">
        <div className="bg-white p-6 rounded-lg shadow-lg dark:bg-gray-700 max-w-4xl w-full">
            <h3 className="text-lg font-semibold mb-4">
                {modalType.includes("create") ? "Create" : "Edit"}{" "}
                {modalType.includes("pakaian") ? "Pakaian" : "Kategori Pakaian"}
            </h3>
            <form onSubmit={handleSubmit}>
                {modalType === "create-pakaian" || modalType === "edit-pakaian" ? (
                    <>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kategori Pakaian
                            </label>
                            <select
                                name="pakaian_kategori_pakaian_id"
                                value={formData.pakaian_kategori_pakaian_id}
                                onChange={handleInputChange}
                                className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                required
                            >
                                <option value="">Select Kategori Pakaian</option>
                                {kategoriPakaian.map((kategori) => (
                                    <option
                                        key={kategori.kategori_pakaian_id}
                                        value={kategori.kategori_pakaian_id}
                                    >
                                        {kategori.kategori_pakaian_nama}
                                    </option>
                                ))}
                            </select>
                            {errors.pakaian_kategori_pakaian_id &&
                                errors.pakaian_kategori_pakaian_id.map(
                                    (error, index) => (
                                        <p
                                            key={index}
                                            className="text-red-500 text-sm mt-1"
                                        >
                                            {error}
                                        </p>
                                    )
                                )}
                        </div>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama
                            </label>
                            <input
                                type="text"
                                name="pakaian_nama"
                                value={formData.pakaian_nama}
                                onChange={handleInputChange}
                                className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                required
                            />
                            {errors.pakaian_nama &&
                                errors.pakaian_nama.map((error, index) => (
                                    <p
                                        key={index}
                                        className="text-red-500 text-sm mt-1"
                                    >
                                        {error}
                                    </p>
                                ))}
                        </div>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga
                            </label>
                            <input
                                type="number"
                                name="pakaian_harga"
                                value={formData.pakaian_harga}
                                onChange={handleInputChange}
                                className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                required
                            />
                            {errors.pakaian_harga &&
                                errors.pakaian_harga.map((error, index) => (
                                    <p
                                        key={index}
                                        className="text-red-500 text-sm mt-1"
                                    >
                                        {error}
                                    </p>
                                ))}
                        </div>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stok
                            </label>
                            <input
                                type="number"
                                name="pakaian_stok"
                                value={formData.pakaian_stok}
                                onChange={handleInputChange}
                                className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                                required
                            />
                            {errors.pakaian_stok &&
                                errors.pakaian_stok.map((error, index) => (
                                    <p
                                        key={index}
                                        className="text-red-500 text-sm mt-1"
                                    >
                                        {error}
                                    </p>
                                ))}
                        </div>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Gambar
                            </label>
                            <input
                                type="file"
                                name="pakaian_gambar_url"
                                onChange={handleFileChange}
                                className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                            />
                            {errors.pakaian_gambar_url &&
                                errors.pakaian_gambar_url.map((error, index) => (
                                    <p
                                        key={index}
                                        className="text-red-500 text-sm mt-1"
                                    >
                                        {error}
                                    </p>
                                ))}
                        </div>
                    </>
                ) : null}
                {modalType === "create-kategori-pakaian" || modalType === "edit-kategori-pakaian" ? (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Kategori Pakaian
                        </label>
                        <input
                            type="text"
                            name="kategori_pakaian_nama"
                            value={formData.kategori_pakaian_nama}
                            onChange={handleInputChange}
                            className="mt-1 p-2 block w-full border rounded-md dark:bg-gray-800 dark:text-gray-300"
                            required
                        />
                        {errors.kategori_pakaian_nama &&
                            errors.kategori_pakaian_nama.map((error, index) => (
                                <p
                                    key={index}
                                    className="text-red-500 text-sm mt-1"
                                >
                                    {error}
                                </p>
                            ))}
                    </div>
                ) : null}
                <div className="flex justify-end">
                    <button
                        type="submit"
                        className="mr-2 p-2 bg-blue-500 text-white rounded"
                    >
                        {modalType.includes("create") ? "Create" : "Update"}
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
