import React, { useState } from 'react';
import { 
  PlusCircle, 
  MessageCircle, 
  ImagePlus, 
  Send, 
  UserCircle 
} from 'lucide-react';

// Componente principal del Foro Estudiantil
const StudentForum = () => {
  const [posts, setPosts] = useState([
    {
      id: 1,
      author: 'María González',
      title: 'Dudas sobre Proyecto de Investigación',
      description: 'Necesito ayuda con la metodología de mi proyecto de investigación en ciencias sociales.',
      image: '/api/placeholder/600/400',
      comments: [
        { id: 1, author: 'Juan Pérez', text: 'Te recomiendo consultar con tu asesor.' }
      ],
      timestamp: '5 minutos atrás'
    },
    {
      id: 2,
      author: 'Carlos Rodríguez',
      title: 'Convocatoria Becas Internacionales',
      description: 'Comparto información sobre nuevas becas para estudios en el extranjero.',
      image: '/api/placeholder/600/400',
      comments: [],
      timestamp: '2 horas atrás'
    }
  ]);

  const [newPost, setNewPost] = useState({
    title: '',
    description: '',
    image: null
  });

  const [showCreateModal, setShowCreateModal] = useState(false);

  const handlePostCreation = () => {
    if (newPost.title && newPost.description) {
      const post = {
        id: posts.length + 1,
        author: 'Usuario Actual',
        title: newPost.title,
        description: newPost.description,
        image: newPost.image || '/api/placeholder/600/400',
        comments: [],
        timestamp: 'Justo ahora'
      };
      setPosts([post, ...posts]);
      setNewPost({ title: '', description: '', image: null });
      setShowCreateModal(false);
    }
  };

  const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setNewPost(prev => ({ ...prev, image: reader.result }));
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="max-w-4xl mx-auto p-4 bg-gray-50">
      {/* Encabezado */}
      <header className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-blue-800">Foro Estudiantil</h1>
        <button 
          onClick={() => setShowCreateModal(true)}
          className="flex items-center bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition"
        >
          <PlusCircle className="mr-2" /> Crear Nuevo Foro
        </button>
      </header>

      {/* Modal de Creación de Publicación */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg w-96">
            <h2 className="text-2xl font-semibold mb-4">Crear Nueva Publicación</h2>
            <input 
              type="text" 
              placeholder="Título del Foro"
              value={newPost.title}
              onChange={(e) => setNewPost(prev => ({ ...prev, title: e.target.value }))}
              className="w-full mb-4 p-2 border rounded"
            />
            <textarea 
              placeholder="Descripción"
              value={newPost.description}
              onChange={(e) => setNewPost(prev => ({ ...prev, description: e.target.value }))}
              className="w-full mb-4 p-2 border rounded h-32"
            />
            <div className="flex items-center mb-4">
              <input 
                type="file" 
                accept="image/*"
                onChange={handleImageUpload}
                className="hidden"
                id="image-upload"
              />
              <label 
                htmlFor="image-upload" 
                className="flex items-center cursor-pointer text-blue-600 hover:text-blue-800"
              >
                <ImagePlus className="mr-2" /> Subir Imagen
              </label>
              {newPost.image && (
                <img 
                  src={newPost.image} 
                  alt="Vista previa" 
                  className="ml-4 w-20 h-20 object-cover rounded"
                />
              )}
            </div>
            <div className="flex justify-end space-x-2">
              <button 
                onClick={() => setShowCreateModal(false)}
                className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
              >
                Cancelar
              </button>
              <button 
                onClick={handlePostCreation}
                className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                Publicar
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Lista de Publicaciones */}
      <div className="space-y-6">
        {posts.map(post => (
          <div 
            key={post.id} 
            className="bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition"
          >
            <div className="flex items-center mb-4">
              <UserCircle className="mr-3 text-blue-600" />
              <div>
                <h2 className="font-semibold text-lg">{post.title}</h2>
                <p className="text-gray-500 text-sm">
                  {post.author} - {post.timestamp}
                </p>
              </div>
            </div>
            
            {post.image && (
              <img 
                src={post.image} 
                alt={post.title} 
                className="w-full h-64 object-cover rounded-md mb-4"
              />
            )}
            
            <p className="text-gray-700 mb-4">{post.description}</p>
            
            <div className="border-t pt-4">
              <div className="flex items-center mb-4">
                <MessageCircle className="mr-2 text-blue-600" />
                <span className="text-gray-600">
                  {post.comments.length} Comentarios
                </span>
              </div>
              
              {post.comments.map(comment => (
                <div 
                  key={comment.id} 
                  className="bg-gray-100 p-3 rounded-lg mb-2"
                >
                  <p className="font-semibold text-sm">{comment.author}</p>
                  <p className="text-gray-700">{comment.text}</p>
                </div>
              ))}
              
              <div className="flex mt-4">
                <input 
                  type="text" 
                  placeholder="Escribe un comentario..."
                  className="flex-grow p-2 border rounded-l-lg"
                />
                <button className="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                  <Send size={20} />
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default StudentForum;
