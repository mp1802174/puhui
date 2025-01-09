document.addEventListener('DOMContentLoaded', function() {
    fetchHierarchyData('city');

    function fetchHierarchyData(level, parentId = null) {
        let url = `/hierarchy?level=${level}`;
        if (parentId) {
            url += `&parent_id=${parentId}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.code === 1) {
                    renderData(level, data.data);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function renderData(level, data) {
        const listId = `${level}-list`;
        const listElement = document.getElementById(listId);
        listElement.innerHTML = '';

        data.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.市行名称 || item.支行名称 || item.核算机构 || item.employee_name} - 余额: ${item.total_balance}`;
            li.addEventListener('click', () => {
                if (level === 'city') {
                    document.getElementById('branch-level').style.display = 'block';
                    fetchHierarchyData('branch', item.市行机构号);
                } else if (level === 'branch') {
                    document.getElementById('accounting-level').style.display = 'block';
                    fetchHierarchyData('accounting', item.支行机构号);
                } else if (level === 'accounting') {
                    document.getElementById('employee-level').style.display = 'block';
                    fetchHierarchyData('employee', item.核算机构编号);
                }
            });
            listElement.appendChild(li);
        });
    }
}); 