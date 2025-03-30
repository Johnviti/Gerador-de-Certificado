
// Script para funcionalidades do sistema de certificados

document.addEventListener('DOMContentLoaded', function() {
  // Verificar se o usuário está na página de login ou já está logado
  const loginPage = document.getElementById('login-page');
  
  // Verificar login
  if (loginPage) {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Mocked login - usuários simulados
        const validUsers = [
          { email: 'admin@unidas.org.br', password: 'admin123' },
          { email: 'tieni@unidas.org.br', password: 'tieni123' },
          { email: 'leandro@unidas.org.br', password: 'leandro123' }
        ];
        
        const user = validUsers.find(user => user.email === email && user.password === password);
        
        if (user) {
          // Salvar estado de autenticação em sessionStorage
          sessionStorage.setItem('isAuthenticated', 'true');
          sessionStorage.setItem('userEmail', email);
          
          // Redirecionar para o dashboard
          window.location.href = 'dashboard.html';
        } else {
          alert('Email ou senha inválidos.');
        }
      });
    }
    
    // Se já estiver autenticado, redirecionar para o dashboard
    if (sessionStorage.getItem('isAuthenticated') === 'true') {
      window.location.href = 'dashboard.html';
    }
  } else {
    // Se estiver em qualquer outra página e não estiver autenticado, redirecionar para login
    if (sessionStorage.getItem('isAuthenticated') !== 'true') {
      window.location.href = 'index.html';
      return; // Para evitar que o resto do código seja executado
    }
    
    // Adicionar nome do usuário
    const userNameElements = document.querySelectorAll('.user-name');
    const userEmail = sessionStorage.getItem('userEmail');
    
    if (userEmail && userNameElements) {
      const shortName = userEmail.split('@')[0];
      const formattedName = shortName.charAt(0).toUpperCase() + shortName.slice(1);
      
      userNameElements.forEach(element => {
        element.textContent = formattedName;
      });
      
      // Iniciais para o avatar
      const userAvatarElements = document.querySelectorAll('.user-avatar');
      const initial = formattedName.charAt(0).toUpperCase();
      
      userAvatarElements.forEach(element => {
        element.textContent = initial;
      });
    }
    
    // Função de logout
    const logoutButtons = document.querySelectorAll('#logout-btn');
    
    if (logoutButtons) {
      logoutButtons.forEach(button => {
        button.addEventListener('click', function() {
          sessionStorage.removeItem('isAuthenticated');
          sessionStorage.removeItem('userEmail');
          window.location.href = 'index.html';
        });
      });
    }
    
    // Ativar tabs na página de upload
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    if (tabButtons && tabPanes) {
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          const tabId = this.getAttribute('data-tab');
          
          // Remover classe active de todas as tabs
          tabButtons.forEach(btn => btn.classList.remove('active'));
          tabPanes.forEach(pane => pane.classList.remove('active'));
          
          // Adicionar classe active à tab clicada
          this.classList.add('active');
          document.getElementById(tabId + '-tab')?.classList.add('active');
        });
      });
    }
    
    // Funcionalidade para o checkbox "selecionar todos" na página de gerar certificados
    const selectAllCheckbox = document.getElementById('select-all');
    const certCheckboxes = document.querySelectorAll('.cert-checkbox');
    
    if (selectAllCheckbox && certCheckboxes.length > 0) {
      selectAllCheckbox.addEventListener('change', function() {
        certCheckboxes.forEach(checkbox => {
          checkbox.checked = selectAllCheckbox.checked;
        });
      });
    }
    
    // Atualizar input de arquivo para mostrar o nome do arquivo selecionado
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    if (fileInputs) {
      fileInputs.forEach(input => {
        input.addEventListener('change', function() {
          const fileName = this.files[0]?.name || 'Nenhum arquivo escolhido';
          const helpText = this.parentElement.nextElementSibling;
          
          if (helpText && helpText.classList.contains('upload-help-text')) {
            if (this.files[0]) {
              helpText.textContent = `Arquivo selecionado: ${fileName}`;
            } else {
              helpText.textContent = 'Nenhum arquivo escolhido';
            }
          }
        });
      });
    }
  }
});
