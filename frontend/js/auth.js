async function login(email, password) {
    try {
        const response = await fetch('http://localhost/vir-data/backend/api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                email: email,
                password: password
            })
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            // Store user data in localStorage or sessionStorage
            localStorage.setItem('user', JSON.stringify(data.data));
            window.location.href = 'dashboard.html';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred during login');
    }
} 