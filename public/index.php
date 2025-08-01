<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\DotEnv;
(new DotEnv(__DIR__ . '/../.env'))->load();
$api_url = $_ENV['API_URL'] ?? 'http://localhost:8001/api.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const API_URL = '<?php echo $api_url; ?>';
    </script>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #a8a29e; /* stone-400 */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #78716c; /* stone-500 */
        }
        /* Base font */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Simple transition for modal and alerts */
        .fade-enter-active, .fade-leave-active {
            transition: all 0.3s ease;
        }
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }
        /* Responsive table styles for mobile and tablet */
        @media (max-width: 1024px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e7e5e4; /* stone-200 */
                border-radius: 0.75rem; /* rounded-xl */
                overflow: hidden;
                background-color: white;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                text-align: right;
                border-bottom: 1px solid #f5f5f4; /* stone-100 */
            }
            .responsive-table td:last-child {
                border-bottom: none;
            }
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #44403c; /* stone-700 */
                text-align: left;
                padding-right: 1rem;
            }
        }
        /* Custom Toggle Switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #ffcc00;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #ffcc00;
        }
    </style>
</head>
<body class="bg-slate-50 antialiased">

    <div id="app">
        <!-- AUTH SCREENS -->
        <div v-if="!isAuthenticated" class="min-h-screen flex flex-col items-center justify-center bg-slate-900 p-4">
            <div class="w-full max-w-md">
                <!-- Login Form -->
                <div v-if="authPage === 'login'" class="bg-white rounded-xl shadow-2xl p-8">
                    <h2 class="text-3xl font-bold text-center text-slate-800 mb-2">Tekrar Hoş Geldiniz</h2>
                    <p class="text-center text-slate-500 mb-8">Paneli yönetmek için giriş yapın.</p>
                    <form @submit.prevent="handleLogin">
                        <div v-if="authError" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg" role="alert">
                            <p>{{ authError }}</p>
                        </div>
                        <div class="mb-4">
                            <label for="login-email" class="block text-slate-700 font-semibold mb-2">E-posta</label>
                            <input v-model="loginData.email" type="email" id="login-email" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <div class="mb-6">
                            <label for="login-password" class="block text-slate-700 font-semibold mb-2">Şifre</label>
                            <input v-model="loginData.password" type="password" id="login-password" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <div class="mb-6">
                            <label for="spam-answer-login" class="block text-slate-700 font-semibold mb-2">Spam Koruması: {{ spamQuestion.num1 }} + {{ spamQuestion.num2 }} = ?</label>
                            <input v-model="spamAnswer" type="number" id="spam-answer-login" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <button type="submit" class="w-full bg-[#ffcc00] text-slate-900 font-bold py-3 px-4 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-lg">Giriş Yap</button>
                    </form>
                    <p v-if="settings.publicRegistration" class="text-center text-sm text-slate-600 mt-6">
                        Hesabın yok mu? <a href="#" @click.prevent="authPage = 'register'; authError = ''" class="font-semibold text-yellow-600 hover:text-yellow-500">Kayıt Ol</a>
                    </p>
                </div>

                <!-- Register Form -->
                <div v-if="authPage === 'register'" class="bg-white rounded-xl shadow-2xl p-8">
                    <h2 class="text-3xl font-bold text-center text-slate-800 mb-2">Hesap Oluştur</h2>
                    <p class="text-center text-slate-500 mb-8">Aramıza katılın.</p>
                    <form @submit.prevent="handleRegister">
                         <div v-if="authError" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg" role="alert">
                            <p>{{ authError }}</p>
                        </div>
                        <div class="mb-4">
                            <label for="register-name" class="block text-slate-700 font-semibold mb-2">Ad Soyad</label>
                            <input v-model="registerData.name" type="text" id="register-name" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <div class="mb-4">
                            <label for="register-email" class="block text-slate-700 font-semibold mb-2">E-posta</label>
                            <input v-model="registerData.email" type="email" id="register-email" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <div class="mb-6">
                            <label for="register-password" class="block text-slate-700 font-semibold mb-2">Şifre</label>
                            <input v-model="registerData.password" type="password" id="register-password" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <div class="mb-6">
                            <label for="spam-answer-register" class="block text-slate-700 font-semibold mb-2">Spam Koruması: {{ spamQuestion.num1 }} + {{ spamQuestion.num2 }} = ?</label>
                            <input v-model="spamAnswer" type="number" id="spam-answer-register" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required>
                        </div>
                        <button type="submit" class="w-full bg-[#ffcc00] text-slate-900 font-bold py-3 px-4 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-lg">Kayıt Ol</button>
                    </form>
                    <p class="text-center text-sm text-slate-600 mt-6">
                        Zaten hesabın var mı? <a href="#" @click.prevent="authPage = 'login'; authError = ''" class="font-semibold text-yellow-600 hover:text-yellow-500">Giriş Yap</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- MAIN DASHBOARD -->
        <div v-if="isAuthenticated" class="relative md:flex h-screen bg-slate-100 w-full">
            <!-- Mobile Sidebar Overlay -->
            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-black opacity-50 z-20 md:hidden"></div>

            <!-- Sidebar -->
            <aside :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 w-64 flex-shrink-0 bg-slate-900 text-slate-300 flex flex-col transform md:relative md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
                <div class="h-16 flex items-center justify-center text-2xl font-bold border-b border-slate-800 text-white">
                    Admin Paneli
                </div>
                <nav class="flex-1 px-4 py-6">
                    <ul>
                        <li><a href="#" @click.prevent="setActivePage('customers'); isSidebarOpen = false" :class="[activePage === 'customers' ? 'bg-[#ffcc00] text-slate-900 font-bold' : 'hover:bg-slate-800 hover:text-white']" class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200"><i class="fa-solid fa-users w-6 text-center mr-3"></i>Müşteriler</a></li>
                        <li class="mt-2"><a href="#" @click.prevent="setActivePage('services'); isSidebarOpen = false" :class="[activePage === 'services' ? 'bg-[#ffcc00] text-slate-900 font-bold' : 'hover:bg-slate-800 hover:text-white']" class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200"><i class="fa-solid fa-concierge-bell w-6 text-center mr-3"></i>Hizmetler</a></li>
                        <li class="mt-2"><a href="#" @click.prevent="setActivePage('users'); isSidebarOpen = false" :class="[activePage === 'users' ? 'bg-[#ffcc00] text-slate-900 font-bold' : 'hover:bg-slate-800 hover:text-white']" class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200"><i class="fa-solid fa-user-shield w-6 text-center mr-3"></i>Kullanıcılar</a></li>
                        <li class="mt-2"><a href="#" @click.prevent="setActivePage('settings'); isSidebarOpen = false" :class="[activePage === 'settings' ? 'bg-[#ffcc00] text-slate-900 font-bold' : 'hover:bg-slate-800 hover:text-white']" class="flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200"><i class="fa-solid fa-cog w-6 text-center mr-3"></i>Ayarlar</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <header class="h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200 flex-shrink-0">
                    <button @click.stop="isSidebarOpen = !isSidebarOpen" class="md:hidden text-slate-500 focus:outline-none">
                        <i class="fa-solid fa-bars w-6 h-6"></i>
                    </button>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-800">{{ pageTitle }}</h1>
                    <div class="relative">
                        <button @click="showProfileDropdown = !showProfileDropdown" class="flex items-center space-x-2">
                            <img :src="userProfile.imageUrl" alt="Profil Resmi" class="w-10 h-10 rounded-full object-cover border-2 border-slate-300">
                        </button>
                        <transition name="fade">
                            <div v-if="showProfileDropdown" @click.away="showProfileDropdown = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
                                <a href="#" @click.prevent="setActivePage('profile'); showProfileDropdown = false" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Profilim</a>
                                <a href="#" @click.prevent="handleLogout" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Çıkış Yap</a>
                            </div>
                        </transition>
                    </div>
                </header>
                
                <main v-if="activePage === 'customers'" class="flex-1 p-6 lg:p-8 overflow-y-auto">
                    <div class="bg-white p-5 rounded-xl shadow-lg mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="relative w-full md:max-w-xs">
                            <input v-model="customerSearchQuery" type="text" placeholder="Müşteri ara..." class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition">
                            <div class="absolute top-0 left-0 inline-flex items-center p-3 text-slate-400">
                                <i class="fa-solid fa-search"></i>
                            </div>
                        </div>
                        <button @click="openAddCustomerModal" class="w-full md:w-auto bg-[#ffcc00] text-slate-900 font-bold py-3 px-5 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md flex items-center justify-center whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Yeni Müşteri Ekle
                        </button>
                    </div>
                    <div class="overflow-x-auto lg:bg-white lg:rounded-xl lg:shadow-lg lg:overflow-hidden">
                        <table class="w-full text-left responsive-table">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th @click="sortCustomersBy('name')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Müşteri Adı <span v-html="getSortIcon('name', customerSortKey, customerSortOrder)"></span></div>
                                    </th>
                                    <th class="p-5 font-semibold text-slate-600">Email</th>
                                    <th class="p-5 font-semibold text-slate-600">Telefon</th>
                                    <th @click="sortCustomersBy('registrationDate')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Kayıt Tarihi <span v-html="getSortIcon('registrationDate', customerSortKey, customerSortOrder)"></span></div>
                                    </th>
                                    <th class="p-5 font-semibold text-slate-600">Durum</th>
                                    <th class="p-5 font-semibold text-slate-600 text-center">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 lg:divide-y-0">
                                <tr v-if="sortedCustomers.length === 0"><td colspan="6" class="p-5 text-center text-slate-500">Müşteri bulunamadı.</td></tr>
                                <tr v-for="customer in sortedCustomers" :key="customer.id" class="lg:border-b lg:border-slate-100">
                                    <td data-label="Müşteri Adı" class="text-slate-800 font-medium p-5">{{ customer.name }}</td>
                                    <td data-label="Email" class="text-slate-600 p-5">{{ customer.email }}</td>
                                    <td data-label="Telefon" class="text-slate-600 p-5">{{ customer.phone }}</td>
                                    <td data-label="Kayıt Tarihi" class="text-slate-600 p-5">{{ customer.registrationDate }}</td>
                                    <td data-label="Durum" class="p-5"><span :class="customer.status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-sm font-semibold rounded-full">{{ customer.status }}</span></td>
                                    <td data-label="İşlemler" class="p-5">
                                        <div class="flex justify-end items-center gap-2">
                                            <button @click="openEditCustomerModal(customer)" class="text-blue-600 hover:text-blue-800 p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-800 p-1"><i class="fa-solid fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </main>
                <main v-if="activePage === 'services'" class="flex-1 p-6 lg:p-8 overflow-y-auto">
                    <div class="bg-white p-5 rounded-xl shadow-lg mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="relative w-full md:max-w-xs">
                            <input v-model="serviceSearchQuery" type="text" placeholder="Hizmet veya müşteri ara..." class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition">
                            <div class="absolute top-0 left-0 inline-flex items-center p-3 text-slate-400">
                                <i class="fa-solid fa-search"></i>
                            </div>
                        </div>
                        <button @click="openAddServiceModal" class="w-full md:w-auto bg-[#ffcc00] text-slate-900 font-bold py-3 px-5 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md flex items-center justify-center whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Yeni Hizmet Ekle
                        </button>
                    </div>
                    <div class="overflow-x-auto lg:bg-white lg:rounded-xl lg:shadow-lg lg:overflow-hidden">
                        <table class="w-full text-left responsive-table">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="p-5 font-semibold text-slate-600">No</th>
                                    <th @click="sortServicesBy('name')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Hizmet Adı <span v-html="getSortIcon('name', serviceSortKey, serviceSortOrder)"></span></div>
                                    </th>
                                    <th @click="sortServicesBy('customerName')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Hizmet Müşteri <span v-html="getSortIcon('customerName', serviceSortKey, serviceSortOrder)"></span></div>
                                    </th>
                                    <th @click="sortServicesBy('endDate')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Hizmet Bitiş <span v-html="getSortIcon('endDate', serviceSortKey, serviceSortOrder)"></span></div>
                                    </th>
                                    <th @click="sortServicesBy('rawRemainingDays')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Kalan Gün <span v-html="getSortIcon('rawRemainingDays', serviceSortKey, serviceSortOrder)"></span></div>
                                    </th>
                                    <th class="p-5 font-semibold text-slate-600 text-center">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 lg:divide-y-0">
                                <tr v-if="sortedServices.length === 0"><td colspan="6" class="p-5 text-center text-slate-500">Hizmet bulunamadı.</td></tr>
                                <tr v-for="(service, index) in sortedServices" :key="service.id" class="lg:border-b lg:border-slate-100">
                                    <td data-label="No" class="p-5 text-slate-600">{{ index + 1 }}</td>
                                    <td data-label="Hizmet Adı" class="p-5 text-slate-800 font-medium">{{ service.name }}</td>
                                    <td data-label="Hizmet Müşteri" class="p-5 text-slate-600">{{ service.customerName }}</td>
                                    <td data-label="Hizmet Bitiş" class="p-5 text-slate-600">{{ formatDate(service.endDate) }}</td>
                                    <td data-label="Kalan Gün" class="p-5 font-semibold" :class="getRemainingDaysColor(service.rawRemainingDays)">{{ service.remainingDaysText }}</td>
                                    <td data-label="İşlemler" class="p-5">
                                         <div class="flex justify-end items-center gap-2">
                                            <button @click="openEditServiceModal(service)" class="text-blue-600 hover:text-blue-800 p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <button @click="deleteService(service.id)" class="text-red-600 hover:text-red-800 p-1"><i class="fa-solid fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </main>
                <main v-if="activePage === 'users'" class="flex-1 p-6 lg:p-8 overflow-y-auto">
                    <div class="bg-white p-5 rounded-xl shadow-lg mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="relative w-full md:max-w-xs">
                            <input v-model="userSearchQuery" type="text" placeholder="Kullanıcı ara..." class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition">
                            <div class="absolute top-0 left-0 inline-flex items-center p-3 text-slate-400">
                                <i class="fa-solid fa-search"></i>
                            </div>
                        </div>
                        <button @click="openAddUserModal" class="w-full md:w-auto bg-[#ffcc00] text-slate-900 font-bold py-3 px-5 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md flex items-center justify-center whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Yeni Kullanıcı Ekle
                        </button>
                    </div>
                    <div class="overflow-x-auto lg:bg-white lg:rounded-xl lg:shadow-lg lg:overflow-hidden">
                        <table class="w-full text-left responsive-table">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th @click="sortUsersBy('name')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Ad Soyad <span v-html="getSortIcon('name', userSortKey, userSortOrder)"></span></div>
                                    </th>
                                    <th @click="sortUsersBy('email')" class="p-5 font-semibold text-slate-600 cursor-pointer hover:bg-slate-100">
                                        <div class="flex items-center gap-2">Email <span v-html="getSortIcon('email', userSortKey, userSortOrder)"></span></div>
                                    </th>
                                    <th class="p-5 font-semibold text-slate-600">Rol</th>
                                    <th class="p-5 font-semibold text-slate-600 text-center">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 lg:divide-y-0">
                                <tr v-if="sortedUsers.length === 0"><td colspan="4" class="p-5 text-center text-slate-500">Kullanıcı bulunamadı.</td></tr>
                                <tr v-for="user in sortedUsers" :key="user.id" class="lg:border-b lg:border-slate-100">
                                    <td data-label="Ad Soyad" class="text-slate-800 font-medium p-5">{{ user.name }}</td>
                                    <td data-label="Email" class="text-slate-600 p-5">{{ user.email }}</td>
                                    <td data-label="Rol" class="p-5"><span class="px-2 py-1 text-sm font-semibold rounded-full" :class="user.role === 'Admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800'">{{ user.role }}</span></td>
                                    <td data-label="İşlemler" class="p-5">
                                        <div class="flex justify-end items-center gap-2">
                                            <button @click="openEditUserModal(user)" class="text-blue-600 hover:text-blue-800 p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-800 p-1" :disabled="user.id === userProfile.id"><i class="fa-solid fa-trash-alt" :class="{ 'text-slate-400 cursor-not-allowed': user.id === userProfile.id }"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </main>
                <main v-if="activePage === 'settings'" class="flex-1 p-6 lg:p-8 overflow-y-auto">
                    <form @submit.prevent="saveSettings">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-1 space-y-8">
                                <div class="bg-white p-8 rounded-xl shadow-lg">
                                    <h3 class="text-xl font-bold text-slate-800 mb-6">Genel Ayarlar</h3>
                                    <div class="space-y-6">
                                        <div>
                                            <label for="siteTitle" class="block text-slate-700 font-semibold mb-2">Site Başlığı</label>
                                            <input v-model="settings.siteTitle" type="text" id="siteTitle" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition">
                                        </div>
                                        <div>
                                            <label for="publicRegistration" class="flex items-center justify-between cursor-pointer">
                                                <span class="text-slate-700 font-semibold">Herkes Kayıt Olabilir</span>
                                                <div class="relative">
                                                    <input type="checkbox" v-model="settings.publicRegistration" id="publicRegistration" class="sr-only">
                                                    <div class="block bg-slate-200 w-14 h-8 rounded-full"></div>
                                                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform" :class="{ 'translate-x-6 bg-yellow-400': settings.publicRegistration }"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white p-8 rounded-xl shadow-lg">
                                    <h3 class="text-xl font-bold text-slate-800 mb-6">Hatırlatma Maili Şablonu</h3>
                                    <div>
                                        <label for="emailTemplate" class="block text-slate-700 font-semibold mb-2">Mail İçeriği</label>
                                        <textarea v-model="settings.emailTemplate" id="emailTemplate" rows="10" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition font-mono text-sm"></textarea>
                                        <p class="text-xs text-slate-500 mt-2">Değişkenler: `{{musteri_adi}}`, `{{hizmet_adi}}`, `{{kalan_gun}}`, `{{iletisim_telefon}}`</p>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-2">
                                <div class="bg-white p-8 rounded-xl shadow-lg">
                                    <h3 class="text-xl font-bold text-slate-800 mb-6">SMTP Ayarları</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div><label for="smtpHost" class="block text-slate-700 font-semibold mb-2">SMTP Sunucusu</label><input v-model="settings.smtp.host" type="text" id="smtpHost" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div><label for="smtpPort" class="block text-slate-700 font-semibold mb-2">SMTP Port</label><input v-model.number="settings.smtp.port" type="number" id="smtpPort" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div><label for="smtpUser" class="block text-slate-700 font-semibold mb-2">Kullanıcı Adı</label><input v-model="settings.smtp.username" type="text" id="smtpUser" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div><label for="smtpPass" class="block text-slate-700 font-semibold mb-2">Şifre</label><input v-model="settings.smtp.password" type="password" id="smtpPass" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div class="md:col-span-2"><label for="smtpSecurity" class="block text-slate-700 font-semibold mb-2">Güvenlik Türü</label><select v-model="settings.smtp.security" id="smtpSecurity" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"><option value="none">Yok</option><option value="ssl">SSL/TLS</option><option value="starttls">STARTTLS</option></select></div>
                                        <div class="md:col-span-2">
                                            <label for="reminderDays" class="block text-slate-700 font-semibold mb-2">Hatırlatma Maili Sıklığı (gün)</label>
                                            <input v-model="settings.reminderDays" type="text" id="reminderDays" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition">
                                            <p class="text-xs text-slate-500 mt-1">Birden fazla gün için virgülle ayırın (örn: 30,15,7).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-3 px-6 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md flex items-center">Ayarları Kaydet</button>
                        </div>
                    </form>
                </main>
                <main v-if="activePage === 'profile'" class="flex-1 p-6 lg:p-8 overflow-y-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1 space-y-8">
                            <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                                <img :src="userProfile.imageUrl" alt="Profil Resmi" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-slate-200">
                                <h3 class="text-2xl font-bold text-slate-800">{{ userProfile.name }}</h3>
                                <p class="text-slate-500">{{ userProfile.email }}</p>
                                <div class="mt-6"><input type="file" accept="image/*" @change="handleProfilePictureUpload" class="hidden" ref="fileInput"><button @click="$refs.fileInput.click()" class="w-full bg-slate-200 text-slate-800 font-bold py-3 px-4 rounded-lg hover:bg-slate-300 transition-colors">Resmi Değiştir</button></div>
                            </div>
                             <div class="bg-white p-8 rounded-xl shadow-lg">
                                <h3 class="text-xl font-bold text-slate-800 mb-6">Veri Yönetimi</h3>
                                <div class="space-y-4">
                                    <button @click="exportData('json')" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"><i class="fa-solid fa-file-code"></i> JSON Olarak Dışa Aktar</button>
                                    <button @click="exportData('csv')" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2"><i class="fa-solid fa-file-csv"></i> CSV Olarak Dışa Aktar</button>
                                    <div class="border-t pt-4">
                                        <label for="importFile" class="block text-slate-700 font-semibold mb-2">JSON'dan İçe Aktar</label>
                                        <input type="file" @change="importData" accept=".json" id="importFile" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-100 file:text-yellow-700 hover:file:bg-yellow-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2 space-y-8">
                            <div class="bg-white p-8 rounded-xl shadow-lg">
                                <h3 class="text-xl font-bold text-slate-800 mb-6">İletişim Bilgileri</h3>
                                <form @submit.prevent="saveContactInfo">
                                    <div class="space-y-4">
                                        <div><label for="contactPhone" class="block text-slate-700 font-semibold mb-2">Telefon</label><input v-model="userProfile.contact.phone" type="tel" id="contactPhone" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div><label for="contactAddress" class="block text-slate-700 font-semibold mb-2">Adres</label><textarea v-model="userProfile.contact.address" id="contactAddress" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></textarea></div>
                                        <div><label for="contactWebsite" class="block text-slate-700 font-semibold mb-2">Web Sitesi</label><input v-model="userProfile.contact.website" type="url" id="contactWebsite" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div>
                                        <div class="flex justify-end"><button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-2 px-5 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md">İletişim Bilgilerini Kaydet</button></div>
                                    </div>
                                </form>
                            </div>
                            <div class="bg-white p-8 rounded-xl shadow-lg">
                                <h3 class="text-xl font-bold text-slate-800 mb-6">Şifreyi Değiştir</h3>
                                <form @submit.prevent="changePassword">
                                    <div class="space-y-4">
                                        <div><label for="currentPassword" class="block text-slate-700 font-semibold mb-2">Mevcut Şifre</label><input v-model="passwordData.current" type="password" id="currentPassword" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div>
                                        <div><label for="newPassword" class="block text-slate-700 font-semibold mb-2">Yeni Şifre</label><input v-model="passwordData.new" type="password" id="newPassword" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div>
                                        <div><label for="confirmPassword" class="block text-slate-700 font-semibold mb-2">Yeni Şifre (Tekrar)</label><input v-model="passwordData.confirm" type="password" id="confirmPassword" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div>
                                        <div class="flex justify-end"><button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-2 px-5 rounded-lg hover:bg-yellow-500 transform hover:scale-105 transition-all duration-300 shadow-md">Şifreyi Değiştir</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- All Modals -->
        <transition name="fade"><div v-if="showCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md"><h2 class="text-2xl font-bold mb-6 text-slate-800">{{ isCustomerEditing ? 'Müşteriyi Düzenle' : 'Yeni Müşteri Ekle' }}</h2><form @submit.prevent="saveCustomer"><div class="mb-4"><label for="c-name" class="block text-slate-700 font-semibold mb-2">Ad Soyad</label><input v-model="currentCustomer.name" type="text" id="c-name" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div class="mb-4"><label for="c-email" class="block text-slate-700 font-semibold mb-2">Email</label><input v-model="currentCustomer.email" type="email" id="c-email" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div class="mb-4"><label for="c-phone" class="block text-slate-700 font-semibold mb-2">Telefon</label><input v-model="currentCustomer.phone" type="tel" id="c-phone" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></div><div class="mb-6"><label for="c-status" class="block text-slate-700 font-semibold mb-2">Durum</label><select v-model="currentCustomer.status" id="c-status" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"><option>Aktif</option><option>Pasif</option></select></div><div class="flex justify-end gap-3"><button type="button" @click="closeCustomerModal" class="bg-slate-200 text-slate-800 font-bold py-2 px-5 rounded-lg hover:bg-slate-300 transition-colors">İptal</button><button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-2 px-5 rounded-lg hover:bg-yellow-500 transition-colors">{{ isCustomerEditing ? 'Güncelle' : 'Kaydet' }}</button></div></form></div></div></transition>
        <transition name="fade"><div v-if="showServiceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md overflow-y-auto" style="max-height: 90vh;"><h2 class="text-2xl font-bold mb-6 text-slate-800">{{ isServiceEditing ? 'Hizmeti Düzenle' : 'Yeni Hizmet Ekle' }}</h2><form @submit.prevent="saveService"><div class="mb-4"><label for="s-name" class="block text-slate-700 font-semibold mb-2">Hizmet Adı</label><input v-model="currentService.name" type="text" id="s-name" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div class="mb-4"><label for="s-customer" class="block text-slate-700 font-semibold mb-2">Hizmet Müşteri</label><select v-model="currentService.customerId" id="s-customer" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required><option disabled value="">Lütfen bir müşteri seçin</option><option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option></select></div><div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4"><div><label for="s-startDate" class="block text-slate-700 font-semibold mb-2">Başlangıç Tarihi</label><input v-model="currentService.startDate" type="date" id="s-startDate" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div><label for="s-endDate" class="block text-slate-700 font-semibold mb-2">Bitiş Tarihi</label><input v-model="currentService.endDate" type="date" id="s-endDate" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div></div><div class="mb-4"><label for="s-desc" class="block text-slate-700 font-semibold mb-2">Açıklama</label><textarea v-model="currentService.description" id="s-desc" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"></textarea></div><div class="mb-6"><label for="s-status" class="block text-slate-700 font-semibold mb-2">Durum</label><select v-model="currentService.status" id="s-status" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"><option>Aktif</option><option>Pasif</option></select></div><div class="flex justify-end gap-3"><button type="button" @click="closeServiceModal" class="bg-slate-200 text-slate-800 font-bold py-2 px-5 rounded-lg hover:bg-slate-300 transition-colors">İptal</button><button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-2 px-5 rounded-lg hover:bg-yellow-500 transition-colors">{{ isServiceEditing ? 'Güncelle' : 'Kaydet' }}</button></div></form></div></div></transition>
        <transition name="fade"><div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md"><h2 class="text-2xl font-bold mb-6 text-slate-800">{{ isUserEditing ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı Ekle' }}</h2><form @submit.prevent="saveUser"><div class="mb-4"><label for="u-name" class="block text-slate-700 font-semibold mb-2">Ad Soyad</label><input v-model="currentUser.name" type="text" id="u-name" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div class="mb-4"><label for="u-email" class="block text-slate-700 font-semibold mb-2">Email</label><input v-model="currentUser.email" type="email" id="u-email" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" required></div><div class="mb-4"><label for="u-password" class="block text-slate-700 font-semibold mb-2">Şifre</label><input v-model="currentUser.password" type="password" id="u-password" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition" :placeholder="isUserEditing ? 'Değiştirmek için doldurun' : ''" :required="!isUserEditing"></div><div class="mb-6"><label for="u-role" class="block text-slate-700 font-semibold mb-2">Rol</label><select v-model="currentUser.role" id="u-role" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:border-transparent transition"><option>Editör</option><option>Admin</option></select></div><div class="flex justify-end gap-3"><button type="button" @click="closeUserModal" class="bg-slate-200 text-slate-800 font-bold py-2 px-5 rounded-lg hover:bg-slate-300 transition-colors">İptal</button><button type="submit" class="bg-[#ffcc00] text-slate-900 font-bold py-2 px-5 rounded-lg hover:bg-yellow-500 transition-colors">{{ isUserEditing ? 'Güncelle' : 'Kaydet' }}</button></div></form></div></div></transition>

        <!-- Success Message -->
        <transition name="fade">
            <div v-if="showSuccessMessage" class="fixed bottom-10 right-10 bg-green-500 text-white py-3 px-6 rounded-lg shadow-lg z-50">
                {{ successMessageText }}
            </div>
        </transition>
    </div>

    <script>
        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                // --- AUTHENTICATION STATE ---
                const isAuthenticated = ref(false);
                const authPage = ref('login'); // 'login' or 'register'
                const authError = ref('');
                const loginData = ref({ email: '', password: '' });
                const registerData = ref({ name: '', email: '', password: '' });
                const spamQuestion = ref({});
                const spamAnswer = ref('');

                // --- GLOBAL STATE ---
                const activePage = ref('customers');
                const showSuccessMessage = ref(false);
                const successMessageText = ref('');
                const showProfileDropdown = ref(false);
                const isSidebarOpen = ref(false);

                const pageTitles = {
                    customers: 'Müşteri Yönetimi',
                    services: 'Hizmet Yönetimi',
                    users: 'Kullanıcı Yönetimi',
                    settings: 'Ayarlar Yönetimi',
                    profile: 'Profil Yönetimi'
                };
                const pageTitle = computed(() => pageTitles[activePage.value] || 'Yönetici Paneli');

                // --- USER & PROFILE MANAGEMENT ---
                const users = ref([
                    { id: 1, name: 'Admin Kullanıcı', email: 'admin@example.com', password: 'password123', role: 'Admin', contact: { phone: '0500 111 2233', address: 'Admin Sk. No:1', website: 'https://admin.com' } },
                    { id: 2, name: 'Editör Ayşe', email: 'editor@example.com', password: 'password123', role: 'Editör', contact: { phone: '0500 444 5566', address: 'Editör Cd. No:2', website: 'https://editor.com' } }
                ]);
                const userProfile = ref({ id: null, name: '', email: '', imageUrl: 'https://placehold.co/256x256/EFEFEF/333?text=AU', contact: { phone: '', address: '', website: '' } });
                const newEmail = ref('');
                const passwordData = ref({ current: '', new: '', confirm: '' });
                const userSearchQuery = ref('');
                const userSortKey = ref('name');
                const userSortOrder = ref('asc');
                const showUserModal = ref(false);
                const isUserEditing = ref(false);
                const defaultUser = { id: null, name: '', email: '', password: '', role: 'Editör', contact: { phone: '', address: '', website: '' } };
                const currentUser = ref({ ...defaultUser });
                
                // --- AUTH METHODS ---
                const generateSpamQuestion = () => {
                    spamQuestion.value = {
                        num1: Math.floor(Math.random() * 10) + 1,
                        num2: Math.floor(Math.random() * 10) + 1,
                    };
                    spamAnswer.value = '';
                };
                
                const validateSpam = () => {
                    const correctAnswer = spamQuestion.value.num1 + spamQuestion.value.num2;
                    if (parseInt(spamAnswer.value) !== correctAnswer) {
                        authError.value = 'Spam koruma sorusu yanlış cevaplandı.';
                        generateSpamQuestion();
                        return false;
                    }
                    return true;
                };

                const handleLogin = () => {
                    authError.value = '';
                    if (!validateSpam()) return;

                    const user = users.value.find(u => u.email === loginData.value.email && u.password === loginData.value.password);
                    if (user) {
                        isAuthenticated.value = true;
                        userProfile.value.id = user.id;
                        userProfile.value.name = user.name;
                        userProfile.value.email = user.email;
                        userProfile.value.contact = { ...user.contact };
                        activePage.value = 'customers';
                    } else {
                        authError.value = 'Geçersiz e-posta veya şifre.';
                        generateSpamQuestion();
                    }
                };

                const handleRegister = () => {
                    authError.value = '';
                    if (!settings.value.publicRegistration) {
                        authError.value = 'Kayıtlar şu anda kapalıdır.';
                        return;
                    }
                    if (!validateSpam()) return;

                    if (users.value.some(u => u.email === registerData.value.email)) {
                        authError.value = 'Bu e-posta adresi zaten kullanılıyor.';
                        generateSpamQuestion();
                        return;
                    }
                    const newUser = {
                        id: Date.now(),
                        name: registerData.value.name,
                        email: registerData.value.email,
                        password: registerData.value.password,
                        role: 'Editör',
                        contact: { phone: '', address: '', website: '' }
                    };
                    users.value.push(newUser);
                    showSuccess('Kayıt başarılı! Lütfen giriş yapın.');
                    authPage.value = 'login';
                    registerData.value = { name: '', email: '', password: '' };
                    generateSpamQuestion();
                };

                const handleLogout = () => {
                    isAuthenticated.value = false;
                    loginData.value = { email: '', password: '' };
                    showProfileDropdown.value = false;
                    generateSpamQuestion();
                };


                const handleProfilePictureUpload = (event) => {
                    const file = event.target.files[0];
                    if (file) {
                        userProfile.value.imageUrl = URL.createObjectURL(file);
                        showSuccess('Profil resmi güncellendi!');
                    }
                };

                const saveContactInfo = () => {
                    const userInDb = users.value.find(u => u.id === userProfile.value.id);
                    if(userInDb) {
                        userInDb.contact = { ...userProfile.value.contact };
                    }
                    showSuccess('İletişim bilgileri güncellendi!');
                };

                const changeEmail = () => {
                    if (newEmail.value && newEmail.value !== userProfile.value.email) {
                        userProfile.value.email = newEmail.value;
                        newEmail.value = '';
                        showSuccess('E-posta başarıyla güncellendi!');
                    }
                };
                const changePassword = () => {
                    if (passwordData.value.new !== passwordData.value.confirm) {
                        alert('Yeni şifreler eşleşmiyor!');
                        return;
                    }
                    if (!passwordData.value.new) {
                        alert('Yeni şifre boş olamaz!');
                        return;
                    }
                    passwordData.value = { current: '', new: '', confirm: '' };
                    showSuccess('Şifre başarıyla değiştirildi!');
                };

                // --- USER MANAGEMENT LOGIC ---
                const filteredUsers = computed(() => {
                    if (!userSearchQuery.value) return users.value;
                    const query = userSearchQuery.value.toLowerCase();
                    return users.value.filter(u => u.name.toLowerCase().includes(query) || u.email.toLowerCase().includes(query));
                });

                const sortedUsers = computed(() => {
                    const key = userSortKey.value;
                    if (!key) return filteredUsers.value;
                    return [...filteredUsers.value].sort((a, b) => {
                        let valA = a[key];
                        let valB = b[key];
                        let comparison = 0;
                        if (valA > valB) comparison = 1;
                        else if (valA < valB) comparison = -1;
                        return userSortOrder.value === 'asc' ? comparison : -comparison;
                    });
                });

                const sortUsersBy = (key) => {
                    if (userSortKey.value === key) {
                        userSortOrder.value = userSortOrder.value === 'asc' ? 'desc' : 'asc';
                    } else {
                        userSortKey.value = key;
                        userSortOrder.value = 'asc';
                    }
                };
                const openAddUserModal = () => { isUserEditing.value = false; currentUser.value = { ...defaultUser }; showUserModal.value = true; };
                const openEditUserModal = (user) => { isUserEditing.value = true; currentUser.value = { ...user, password: '' }; showUserModal.value = true; };
                const closeUserModal = () => { showUserModal.value = false; };
                const saveUser = () => {
                    if (isUserEditing.value) {
                        const index = users.value.findIndex(u => u.id === currentUser.value.id);
                        if (index !== -1) {
                            const oldPassword = users.value[index].password;
                            users.value[index] = { ...currentUser.value, password: currentUser.value.password || oldPassword };
                        }
                    } else {
                        currentUser.value.id = Date.now();
                        users.value.push({ ...currentUser.value });
                    }
                    closeUserModal();
                    showSuccess(isUserEditing.value ? 'Kullanıcı güncellendi!' : 'Kullanıcı eklendi!');
                };
                const deleteUser = (id) => {
                    if (id === userProfile.value.id) {
                        alert('Kendinizi silemezsiniz!');
                        return;
                    }
                    users.value = users.value.filter(u => u.id !== id);
                };

                // --- CUSTOMER MANAGEMENT STATE & LOGIC ---
                const customers = ref([
                    { id: 1, name: 'Ahmet Yılmaz', email: 'ahmet.yilmaz@example.com', phone: '0555 123 4567', registrationDate: '2024-01-15', status: 'Aktif' },
                    { id: 2, name: 'Ayşe Kaya', email: 'ayse.kaya@example.com', phone: '0544 987 6543', registrationDate: '2024-02-20', status: 'Aktif' },
                ]);
                const customerSearchQuery = ref('');
                const customerSortKey = ref('');
                const customerSortOrder = ref('asc');

                const filteredCustomers = computed(() => {
                    if (!customerSearchQuery.value) return customers.value;
                    const query = customerSearchQuery.value.toLowerCase();
                    return customers.value.filter(c => c.name.toLowerCase().includes(query) || c.email.toLowerCase().includes(query) || c.phone.includes(query));
                });

                const sortedCustomers = computed(() => {
                    const key = customerSortKey.value;
                    if (!key) return filteredCustomers.value;
                    
                    return [...filteredCustomers.value].sort((a, b) => {
                        let valA = a[key];
                        let valB = b[key];
                        
                        let comparison = 0;
                        if (valA > valB) {
                            comparison = 1;
                        } else if (valA < valB) {
                            comparison = -1;
                        }
                        return customerSortOrder.value === 'asc' ? comparison : -comparison;
                    });
                });

                const sortCustomersBy = (key) => {
                    if (customerSortKey.value === key) {
                        customerSortOrder.value = customerSortOrder.value === 'asc' ? 'desc' : 'asc';
                    } else {
                        customerSortKey.value = key;
                        customerSortOrder.value = 'asc';
                    }
                };

                const showCustomerModal = ref(false);
                const isCustomerEditing = ref(false);
                const defaultCustomer = { id: null, name: '', email: '', phone: '', status: 'Aktif', registrationDate: '' };
                const currentCustomer = ref({ ...defaultCustomer });
                
                const openAddCustomerModal = () => { isCustomerEditing.value = false; currentCustomer.value = { ...defaultCustomer }; showCustomerModal.value = true; };
                const openEditCustomerModal = (customer) => { isCustomerEditing.value = true; currentCustomer.value = { ...customer }; showCustomerModal.value = true; };
                const closeCustomerModal = () => { showCustomerModal.value = false; };
                const saveCustomer = () => {
                    if (isCustomerEditing.value) {
                        const index = customers.value.findIndex(c => c.id === currentCustomer.value.id);
                        if (index !== -1) customers.value[index] = { ...currentCustomer.value };
                    } else {
                        currentCustomer.value.id = Date.now();
                        currentCustomer.value.registrationDate = new Date().toISOString().split('T')[0];
                        customers.value.unshift({ ...currentCustomer.value });
                    }
                    closeCustomerModal();
                    showSuccess(isCustomerEditing.value ? 'Müşteri güncellendi!' : 'Müşteri eklendi!');
                };
                const deleteCustomer = (id) => { customers.value = customers.value.filter(c => c.id !== id); };

                // --- SERVICE MANAGEMENT STATE & LOGIC ---
                const services = ref([
                    { id: 1, name: 'Web Tasarım Paketi', description: 'Modern ve mobil uyumlu web sitesi tasarımı.', status: 'Aktif', customerId: 1, customerName: 'Ahmet Yılmaz', startDate: '2025-01-15', endDate: '2026-01-15', duration: 365 },
                    { id: 2, name: 'SEO Danışmanlığı', description: 'Arama motoru optimizasyonu.', status: 'Aktif', customerId: 2, customerName: 'Ayşe Kaya', startDate: '2025-07-01', endDate: '2025-07-15', duration: 14 },
                ]);
                const serviceSearchQuery = ref('');
                const serviceSortKey = ref('');
                const serviceSortOrder = ref('asc');

                const filteredServices = computed(() => {
                    const query = serviceSearchQuery.value.toLowerCase();
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const filtered = services.value.filter(s => s.name.toLowerCase().includes(query) || (s.customerName && s.customerName.toLowerCase().includes(query)));
                    return filtered.map(service => {
                        if (!service.endDate) return { ...service, rawRemainingDays: Infinity, remainingDaysText: 'Tarih Belirtilmemiş' };
                        const endDate = new Date(service.endDate);
                        endDate.setHours(0,0,0,0);
                        const timeDiff = endDate.getTime() - today.getTime();
                        const remainingDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                        let remainingDaysText;
                        if (remainingDays < 0) remainingDaysText = 'Süresi Doldu';
                        else if (remainingDays === 0) remainingDaysText = 'Son Gün';
                        else remainingDaysText = `${remainingDays} gün`;
                        return { ...service, rawRemainingDays: remainingDays, remainingDaysText: remainingDaysText };
                    });
                });

                const sortedServices = computed(() => {
                    const key = serviceSortKey.value;
                    if (!key) return filteredServices.value;
                    
                    return [...filteredServices.value].sort((a, b) => {
                        let valA = a[key];
                        let valB = b[key];
                        
                        let comparison = 0;
                        if (valA > valB) {
                            comparison = 1;
                        } else if (valA < valB) {
                            comparison = -1;
                        }
                        return serviceSortOrder.value === 'asc' ? comparison : -comparison;
                    });
                });

                const sortServicesBy = (key) => {
                    if (serviceSortKey.value === key) {
                        serviceSortOrder.value = serviceSortOrder.value === 'asc' ? 'desc' : 'asc';
                    } else {
                        serviceSortKey.value = key;
                        serviceSortOrder.value = 'asc';
                    }
                };

                const showServiceModal = ref(false);
                const isServiceEditing = ref(false);
                const defaultService = { id: null, name: '', description: '', status: 'Aktif', customerId: '', customerName: '', startDate: '', endDate: '', duration: 0 };
                const currentService = ref({ ...defaultService });
                
                const getRemainingDaysColor = (days) => {
                    if (days < 0) return 'text-red-600';
                    if (days <= 7) return 'text-orange-500';
                    return 'text-green-600';
                };
                const formatDate = (dateString) => {
                    if (!dateString) return '';
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return new Date(dateString).toLocaleDateString('tr-TR', options);
                };
                const openAddServiceModal = () => { isServiceEditing.value = false; currentService.value = { ...defaultService }; showServiceModal.value = true; };
                const openEditServiceModal = (service) => { isServiceEditing.value = true; currentService.value = { ...service }; showServiceModal.value = true; };
                const closeServiceModal = () => { showServiceModal.value = false; };
                const saveService = () => {
                    const selectedCustomer = customers.value.find(c => c.id === currentService.value.customerId);
                    if (selectedCustomer) currentService.value.customerName = selectedCustomer.name;
                    if (currentService.value.startDate && currentService.value.endDate) {
                        const start = new Date(currentService.value.startDate);
                        const end = new Date(currentService.value.endDate);
                        currentService.value.duration = Math.ceil((end - start) / (1000 * 3600 * 24));
                    }
                    if (isServiceEditing.value) {
                        const index = services.value.findIndex(s => s.id === currentService.value.id);
                        if (index !== -1) services.value[index] = { ...currentService.value };
                    } else {
                        currentService.value.id = Date.now();
                        services.value.unshift({ ...currentService.value });
                    }
                    closeServiceModal();
                    showSuccess(isServiceEditing.value ? 'Hizmet güncellendi!' : 'Hizmet eklendi!');
                };
                const deleteService = (id) => { services.value = services.value.filter(s => s.id !== id); };

                // --- SETTINGS MANAGEMENT STATE & LOGIC ---
                const settings = ref({
                    siteTitle: 'Benim Harika Sitem',
                    publicRegistration: true,
                    reminderDays: '30,15,7',
                    emailTemplate: 'Sayın {{musteri_adi}},\n\n{{hizmet_adi}} adlı hizmetinizin sona ermesine {{kalan_gun}} gün kalmıştır.\n\nHizmetinizi yenilemek için bizimle iletişime geçebilirsiniz.\n\nTelefon: {{iletisim_telefon}}\nWeb: {{iletisim_website}}',
                    smtp: { host: 'smtp.example.com', port: 587, username: 'user@example.com', password: '', security: 'starttls' }
                });
                const saveSettings = () => {
                    console.log('Ayarlar kaydediliyor:', JSON.parse(JSON.stringify(settings.value)));
                    showSuccess('Ayarlar başarıyla kaydedildi!');
                };

                // --- DATA EXPORT/IMPORT ---
                const exportData = (format) => {
                    const data = {
                        users: users.value,
                        customers: customers.value,
                        services: services.value,
                        settings: settings.value
                    };

                    if (format === 'json') {
                        const jsonString = `data:text/json;charset=utf-8,${encodeURIComponent(JSON.stringify(data, null, 2))}`;
                        const link = document.createElement("a");
                        link.href = jsonString;
                        link.download = "data.json";
                        link.click();
                    } else if (format === 'csv') {
                        // Export Customers
                        const customerCSV = convertToCSV(customers.value);
                        const customerBlob = new Blob([customerCSV], { type: 'text/csv;charset=utf-8;' });
                        const customerLink = document.createElement("a");
                        customerLink.href = URL.createObjectURL(customerBlob);
                        customerLink.download = "customers.csv";
                        customerLink.click();

                        // Export Services
                        const serviceCSV = convertToCSV(services.value);
                        const serviceBlob = new Blob([serviceCSV], { type: 'text/csv;charset=utf-8;' });
                        const serviceLink = document.createElement("a");
                        serviceLink.href = URL.createObjectURL(serviceBlob);
                        serviceLink.download = "services.csv";
                        serviceLink.click();
                    }
                };

                const convertToCSV = (arr) => {
                    if (arr.length === 0) return "";
                    const array = [Object.keys(arr[0])].concat(arr);
                    return array.map(it => {
                        return Object.values(it).toString();
                    }).join('\n');
                };

                const importData = (event) => {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        try {
                            const data = JSON.parse(e.target.result);
                            if (data.users) users.value = data.users;
                            if (data.customers) customers.value = data.customers;
                            if (data.services) services.value = data.services;
                            if (data.settings) settings.value = data.settings;
                            showSuccess('Veriler başarıyla içe aktarıldı!');
                        } catch (error) {
                            alert('Geçersiz JSON dosyası!');
                            console.error("JSON parse error:", error);
                        }
                    };
                    reader.readAsText(file);
                };

                // --- UTILITY FUNCTIONS ---
                const showSuccess = (message) => {
                    successMessageText.value = message;
                    showSuccessMessage.value = true;
                    setTimeout(() => { showSuccessMessage.value = false; }, 3000);
                };
                
                const getSortIcon = (key, sortKey, sortOrder) => {
                    if (key !== sortKey) {
                        return `<i class="fa-solid fa-sort text-slate-400"></i>`;
                    }
                    if (sortOrder === 'asc') {
                        return `<i class="fa-solid fa-sort-up text-slate-800"></i>`;
                    } else {
                        return `<i class="fa-solid fa-sort-down text-slate-800"></i>`;
                    }
                };

                // --- PAGE NAVIGATION ---
                const setActivePage = (page) => { activePage.value = page; };

                onMounted(() => {
                    generateSpamQuestion();
                });

                return {
                    isAuthenticated, authPage, authError, loginData, registerData, handleLogin, handleRegister, handleLogout, spamQuestion, spamAnswer,
                    activePage, setActivePage, pageTitle,
                    customers, customerSearchQuery, sortedCustomers, customerSortKey, customerSortOrder, sortCustomersBy, showCustomerModal, isCustomerEditing, currentCustomer, openAddCustomerModal, openEditCustomerModal, closeCustomerModal, saveCustomer, deleteCustomer,
                    services, serviceSearchQuery, sortedServices, serviceSortKey, serviceSortOrder, sortServicesBy, showServiceModal, isServiceEditing, currentService, openAddServiceModal, openEditServiceModal, closeServiceModal, saveService, deleteService,
                    users, userSearchQuery, sortedUsers, userSortKey, userSortOrder, sortUsersBy, showUserModal, isUserEditing, currentUser, openAddUserModal, openEditUserModal, closeUserModal, saveUser, deleteUser,
                    settings, saveSettings, saveContactInfo,
                    userProfile, newEmail, passwordData, handleProfilePictureUpload, changeEmail, changePassword,
                    showSuccessMessage, successMessageText, showProfileDropdown,
                    getRemainingDaysColor, formatDate, isSidebarOpen, getSortIcon,
                    exportData, importData
                };
            }
        }).mount('#app');
    </script>

</body>
</html>
