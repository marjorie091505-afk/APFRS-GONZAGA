<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Airline System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-[#0a0a0a] text-white flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 bg-[#121212] border border-gray-800 rounded-lg shadow-2xl">

        <!-- Red Laravel Logo -->
        <div class="flex justify-center mb-6">
            <svg class="w-16 h-16 text-red-600" viewBox="0 0 316 316" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.305 305.37 80.115C303.87 76.535 299.85 74.815 296.4 76.515C264.79 91.685 200.29 92.685 176.94 91.035C162.39 90.035 146.06 88.195 130.47 83.565C130.17 83.465 129.88 83.345 129.58 83.245C118.64 79.855 108.11 78.155 97.89 78.155C87.71 78.155 77.92 79.795 68.9 82.985C59.93 86.155 51.56 90.745 44.25 96.555C36.94 102.365 30.64 109.395 25.91 117.155C21.17 124.925 17.94 133.275 16.4 142.085C14.85 150.895 15.11 159.955 17.15 168.585C19.2 177.215 22.94 185.385 28.03 192.585C33.13 199.785 39.53 205.985 46.64 210.855C53.76 215.735 61.63 219.225 69.85 221.185C78.06 223.145 86.46 223.545 94.82 222.375C103.19 221.205 111.19 218.415 118.49 214.115C125.79 209.815 132.23 204.005 137.89 197.235C143.55 190.465 148.23 182.885 151.78 174.715C155.32 166.545 157.62 157.955 158.54 149.185C159.46 140.415 158.97 131.415 157.12 122.765C155.26 114.115 152.08 105.815 147.8 98.115C143.52 90.415 138.17 83.575 131.97 77.615C125.77 71.655 118.72 66.595 111.02 62.625C103.33 58.655 95.13 55.835 86.63 54.125C78.13 52.415 69.3 51.845 60.66 52.485C52.02 53.125 43.55 54.965 35.66 57.875C27.77 60.785 20.43 64.685 13.99 69.475C7.55 74.265 1.97 79.945 -2.39 86.275C-6.76 92.605 -9.97 99.485 -11.78 106.695C-13.59 113.905 -13.98 121.425 -12.92 128.985C-11.86 136.545 -9.32 143.925 -5.53 150.785C-1.75 157.645 3.19 163.865 9.08 169.175C14.97 174.485 21.72 178.785 28.98 181.855C36.23 184.925 43.98 186.715 51.78 187.095C59.59 187.475 67.65 186.455 75.37 184.035C83.09 181.615 90.39 177.835 97.05 172.875C103.71 167.915 109.62 161.835 114.47 154.995C119.33 148.155 123.07 140.645 125.53 132.755C127.99 124.865 129.13 116.695 128.89 108.575C128.65 100.455 127.05 92.445 124.16 84.955C121.27 77.465 117.14 70.585 111.99 64.635C106.84 58.685 100.76 53.805 93.91 50.155C87.06 46.505 79.6 44.165 71.93 43.315C64.27 42.465 56.45 43.125 48.99 45.235C41.54 47.345 34.57 50.855 28.41 55.525C22.25 60.195 16.97 65.915 12.77 72.285C8.57 78.655 5.51 85.545 3.71 92.735C1.91 99.925 1.39 107.275 2.21 114.465C3.02 121.655 5.14 128.545 8.41 134.785C11.68 141.025 15.98 146.505 21.06 150.955C26.14 155.405 32.01 158.815 38.32 160.955C44.64 163.095 51.27 163.955 57.82 162.455" fill="none" stroke="currentColor" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h2 class="text-center text-xl font-semibold mb-6">Create an Account</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full px-4 py-2 bg-[#1a1a1a] border border-gray-700 rounded-md focus:outline-none focus:border-red-500 text-white">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 bg-[#1a1a1a] border border-gray-700 rounded-md focus:outline-none focus:border-red-500 text-white">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 bg-[#1a1a1a] border border-gray-700 rounded-md focus:outline-none focus:border-red-500 text-white">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-2 bg-[#1a1a1a] border border-gray-700 rounded-md focus:outline-none focus:border-red-500 text-white">
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white">Already registered?</a>
                <button type="submit" class="bg-white text-black px-6 py-2 rounded-md font-semibold hover:bg-gray-200 transition">Register</button>
            </div>
        </form>
    </div>
</body>
</html>
