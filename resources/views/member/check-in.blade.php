<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Check-In/Check-Out</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Check-In / Check-Out</h1>

        <form id="checkInForm" class="space-y-4">
            @csrf

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number
                </label>
                <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">
                    PIN
                </label>
                <input type="password" id="pin" name="pin" required placeholder="Enter your PIN"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Submit
            </button>
        </form>

        <!-- Status Display Area -->
        <div id="statusArea" class="mt-6 hidden">
            <div id="errorAlert" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p id="errorMessage" class="text-red-800 text-sm"></p>
            </div>

            <div id="successAlert" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-center">
                    <p id="statusMessage" class="text-green-800 font-semibold text-lg mb-4"></p>
                    <div class="text-left space-y-3">
                        <div>
                            <p class="text-gray-600 text-xs uppercase font-semibold">Member Name</p>
                            <p id="memberName" class="text-gray-900 font-semibold text-lg"></p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs uppercase font-semibold">Check-In Time</p>
                            <p id="checkInTime" class="text-gray-900"></p>
                        </div>
                        <div id="checkOutInfo" class="hidden">
                            <div>
                                <p class="text-gray-600 text-xs uppercase font-semibold">Check-Out Time</p>
                                <p id="checkOutTime" class="text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs uppercase font-semibold">Duration Worked</p>
                                <p id="durationWorked" class="text-gray-900"></p>
                            </div>
                        </div>
                        <div class="border-t border-green-200 pt-3 mt-3">
                            <p class="text-gray-600 text-xs uppercase font-semibold">Remaining Hours</p>
                            <p id="remainingHours" class="text-green-700 font-bold text-xl"></p>
                        </div>
                    </div>
                </div>

                <button type="button" id="resetBtn"
                    class="w-full mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200">
                    New Check-In
                </button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('checkInForm');
        const submitBtn = document.getElementById('submitBtn');
        const statusArea = document.getElementById('statusArea');
        const errorAlert = document.getElementById('errorAlert');
        const successAlert = document.getElementById('successAlert');
        const errorMessage = document.getElementById('errorMessage');
        const resetBtn = document.getElementById('resetBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            try {
                const formData = new FormData(form);
                const response = await fetch('{{ route('member.checkin.process') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData)),
                });

                const data = await response.json();

                if (data.success) {
                    displaySuccess(data);
                } else {
                    displayError(data.message);
                }
            } catch (error) {
                displayError('An error occurred. Please try again.');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
            }
        });

        function displaySuccess(data) {
            errorAlert.classList.add('hidden');
            successAlert.classList.remove('hidden');
            statusArea.classList.remove('hidden');

            const statusMessage = data.status === 'checked_in' ? '✓ Checked In' : '✓ Checked Out';
            document.getElementById('statusMessage').textContent = statusMessage;
            document.getElementById('memberName').textContent = data.member.name;

            const checkInTime = new Date(data.session.check_in_at);
            document.getElementById('checkInTime').textContent = checkInTime.toLocaleString();

            const remainingHours = data.remaining_hours !== null ? parseFloat(data.remaining_hours).toFixed(2) : 'N/A';
            document.getElementById('remainingHours').textContent = remainingHours + ' hours';

            const checkOutInfo = document.getElementById('checkOutInfo');
            if (data.status === 'checked_out') {
                checkOutInfo.classList.remove('hidden');
                const checkOutTime = new Date(data.session.check_out_at);
                document.getElementById('checkOutTime').textContent = checkOutTime.toLocaleString();
                document.getElementById('durationWorked').textContent = data.duration_worked_hours + ' hours (' + data
                    .duration_worked_minutes + ' minutes)';
            } else {
                checkOutInfo.classList.add('hidden');
            }

            form.classList.add('hidden');
        }

        function displayError(message) {
            errorAlert.classList.remove('hidden');
            successAlert.classList.add('hidden');
            errorMessage.textContent = message;
            statusArea.classList.remove('hidden');
        }

        resetBtn.addEventListener('click', () => {
            form.reset();
            form.classList.remove('hidden');
            statusArea.classList.add('hidden');
            errorAlert.classList.add('hidden');
            successAlert.classList.add('hidden');
        });
    </script>
</body>

</html>
