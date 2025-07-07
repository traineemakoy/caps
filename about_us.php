<!-- Google Font Link (Roboto) -->
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

<section class="team-section">
  <div class="container">

    <!-- ✅ Adviser (Top Center) -->
    <div class="centered">
      <div class="card">
        <img src="assets/img/sir.jpg" alt="Prof. Esmundo Jr Tonio">
        <div class="info">
          <h4>Prof. Esmundo Jr Tonio</h4>
          <p>Capstone Adviser</p>
        </div>
      </div>
    </div>

    <!-- ✅ Team Members -->
    <div class="members-row">
      <!-- Member 1 -->
      <div class="card">
        <img src="assets/img/lara.jpg" alt="Lara Mae Gimeno">
        <div class="info">
          <h4>Lara Mae Gimeno</h4>
          <p></p>
        </div>
      </div>

      <!-- Member 2 -->
      <div class="card">
        <img src="assets/img/pogi.jpg" alt="Michelangelo T. Testigo">
        <div class="info">
          <h4>Michelangelo T. Testigo</h4>
          <p></p>
        </div>
      </div>

      <!-- Member 3 -->
      <div class="card">
        <img src="assets/img/mai.jpg" alt="Kishida Mai Zuñiga">
        <div class="info">
          <h4>Kishida Mai Zuñiga</h4>
          <p></p>
        </div>
      </div>
    </div>

    <!-- ✅ Paragraph Below -->
    <div class="paragraph-below">
      <p>
        This Capstone 2 project was developed to address the growing need for a more efficient, secure, and intelligent system for managing student records, grades, and academic processes. Guided by our adviser, Prof. Esmundo Jr. Tonio, our team collaborated to design a solution that streamlines administrative workflows and enhances user experience for students, faculty, and staff. Each member contributed their expertise to ensure the system is functional, scalable, and aligned with the needs of the institution. This project reflects our commitment to innovation, responsibility, and excellence in the field of Information Technology.
      </p>
    </div>

  </div>
</section>

<style>
    * {
  font-family: 'Roboto', sans-serif;
}

.team-section {
  background: #e6f4ea;
  padding: 50px 20px;
}

.container {
  max-width: 1200px;
  margin: auto;
}

.centered {
  display: flex;
  justify-content: center;
  margin-bottom: 40px;
}

.members-row {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 40px;
}

.paragraph-below {
  text-align: center;
  max-width: 800px;
  margin: auto;
  font-size: 16px;
  line-height: 1.6;
  color: #333;
}

.card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  overflow: hidden;
  width: 230px;
  text-align: center;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
}

.card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  object-position: top;
}

.info {
  background: #16a34a;
  color: white;
  padding: 15px;
  border-top: 2px solid white;
}

</style>